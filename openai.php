<?php
/**
 * OpenAI API Integration
 *
 * Functions for interacting with OpenAI API to generate quiz content
 */

/**
 * Generate quiz recommendations based on lesson details
 *
 * @param string $lesson_topic - The main topic of the lesson
 * @param string $subjects - Related subjects or subtopics
 * @return array - Array of quiz recommendations with difficulties
 */
function generate_quiz_recommendations($lesson_topic, $subjects) {
    $prompt = "Create 3 quiz suggestions for the topic '{$lesson_topic}' with subjects: {$subjects}.

Return ONLY a JSON array with exactly 3 objects. Each object must have these exact fields:
- title: string (quiz title)
- description: string (brief description)
- difficulty: string (exactly 'beginner', 'intermediate', or 'advanced')
- num_questions: number (5-10)
- question_types: array of strings (from: 'mcq', 'true_false', 'multiple_select')

Example format:
[
  {
    \"title\": \"Basic Algebra Quiz\",
    \"description\": \"Test fundamental algebra concepts\",
    \"difficulty\": \"beginner\",
    \"num_questions\": 8,
    \"question_types\": [\"mcq\", \"true_false\"]
  }
]";

    $response = call_openai_api($prompt);

    if (!$response) {
        error_log("OpenAI API call failed for quiz recommendations");
        return false;
    }

    // Clean the response - remove any markdown formatting
    $response = trim($response);
    if (strpos($response, '```json') === 0) {
        $response = substr($response, 7);
    }
    if (strrpos($response, '```') === strlen($response) - 3) {
        $response = substr($response, 0, -3);
    }
    $response = trim($response);

    // Parse the JSON response
    $data = json_decode($response, true);

    if (json_last_error() !== JSON_ERROR_NONE) {
        error_log("JSON decode error: " . json_last_error_msg() . " for response: " . $response);
        return false;
    }

    // Validate the structure
    if (!is_array($data) || count($data) !== 3) {
        error_log("Invalid quiz recommendations structure: " . print_r($data, true));
        return false;
    }

    foreach ($data as $quiz) {
        if (!isset($quiz['title'], $quiz['description'], $quiz['difficulty'], $quiz['num_questions'], $quiz['question_types'])) {
            error_log("Missing required fields in quiz recommendation: " . print_r($quiz, true));
            return false;
        }
    }

    return $data;
}

/**
 * Generate questions for a specific quiz
 *
 * @param string $quiz_title - The quiz title
 * @param string $description - Quiz description
 * @param string $difficulty - Difficulty level
 * @param int $num_questions - Number of questions to generate
 * @param array $question_types - Array of question types to include
 * @return array - Array of generated questions
 */
function generate_quiz_questions($quiz_title, $description, $difficulty, $num_questions, $question_types) {
    $types_str = implode(', ', $question_types);

    $prompt = "Generate {$num_questions} quiz questions for: '{$quiz_title}'
Description: {$description}
Difficulty: {$difficulty}
Question types to include: {$types_str}

Return ONLY a JSON array of question objects. Each question object must have:
- question_text: string
- question_type: string ('mcq', 'true_false', or 'multiple_select')
- points: number (1-5)
- For MCQ: options (array of 4 strings) and correct_answer_index (number 0-3)
- For true_false: correct_answer (boolean)
- For multiple_select: options (array of 4-5 strings) and correct_answer_indices (array of numbers)";

    $response = call_openai_api($prompt);

    if (!$response) {
        error_log("OpenAI API call failed for quiz questions");
        return false;
    }

    // Clean the response
    $response = trim($response);
    if (strpos($response, '```json') === 0) {
        $response = substr($response, 7);
    }
    if (strrpos($response, '```') === strlen($response) - 3) {
        $response = substr($response, 0, -3);
    }
    $response = trim($response);

    $data = json_decode($response, true);

    if (json_last_error() !== JSON_ERROR_NONE) {
        error_log("JSON decode error for questions: " . json_last_error_msg() . " for response: " . $response);
        return false;
    }

    return $data;
}

/**
 * Call OpenAI API
 *
 * @param string $prompt - The prompt to send to OpenAI
 * @return string|false - API response or false on error
 */
function call_openai_api($prompt) {
    $api_key = OPENAI_API_KEY;
    $model = OPENAI_MODEL;

    if (empty($api_key)) {
        error_log("OpenAI API key not configured");
        return false;
    }

    $data = [
        'model' => $model,
        'messages' => [
            [
                'role' => 'user',
                'content' => $prompt
            ]
        ],
        'max_tokens' => OPENAI_MAX_TOKENS,
        'temperature' => OPENAI_TEMPERATURE
    ];

    $ch = curl_init('https://api.openai.com/v1/chat/completions');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Authorization: Bearer ' . $api_key
    ]);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);

    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($http_code !== 200) {
        error_log("OpenAI API error: HTTP {$http_code} - {$response}");
        return false;
    }

    $result = json_decode($response, true);

    if (!isset($result['choices'][0]['message']['content'])) {
        error_log("Invalid OpenAI API response: " . $response);
        return false;
    }

    return $result['choices'][0]['message']['content'];
}

/**
 * Save generated quiz to database
 *
 * @param int $course_id - Course ID
 * @param array $quiz_data - Quiz data from AI
 * @param array $questions - Generated questions
 * @return int|false - Quiz ID or false on error
 */
function save_generated_quiz($course_id, $quiz_data, $questions) {
    global $conn;

    // Start transaction
    db_begin_transaction($conn);

    try {
        // Insert quiz
        $sql = "INSERT INTO quizzes (course_id, title, description, passing_score, time_limit, max_attempts, show_correct_answers)
                VALUES (?, ?, ?, ?, ?, ?, ?)";

        $difficulty = strtolower($quiz_data['difficulty']);
        $time_limit = $difficulty === 'beginner' ? 20 : ($difficulty === 'intermediate' ? 30 : 45);
        $passing_score = $difficulty === 'beginner' ? 60 : ($difficulty === 'intermediate' ? 70 : 80);

        $result = db_query($conn, $sql, "issiiii", [
            $course_id,
            $quiz_data['title'],
            $quiz_data['description'],
            $passing_score,
            $time_limit,
            null, // max_attempts
            1 // show_correct_answers
        ]);

        if (!$result) {
            throw new Exception("Failed to create quiz");
        }

        $quiz_id = db_insert_id($conn);

        // Insert questions
        $question_order = 1;
        foreach ($questions as $question) {
            $sql = "INSERT INTO questions (quiz_id, question_text, question_type, points, question_order)
                    VALUES (?, ?, ?, ?, ?)";

            $result = db_query($conn, $sql, "issii", [
                $quiz_id,
                $question['question_text'],
                $question['question_type'],
                $question['points'],
                $question_order++
            ]);

            if (!$result) {
                throw new Exception("Failed to create question");
            }

            $question_id = db_insert_id($conn);

            // Insert answers based on question type
            if ($question['question_type'] === 'mcq') {
                foreach ($question['options'] as $index => $option) {
                    $is_correct = ($index === $question['correct_answer_index']) ? 1 : 0;
                    $sql = "INSERT INTO answers (question_id, answer_text, is_correct, answer_order)
                            VALUES (?, ?, ?, ?)";
                    db_query($conn, $sql, "ssii", [$question_id, $option, $is_correct, $index + 1]);
                }
            } elseif ($question['question_type'] === 'true_false') {
                $true_answer = ($question['correct_answer']) ? 'True' : 'False';
                $false_answer = ($question['correct_answer']) ? 'False' : 'True';

                $sql = "INSERT INTO answers (question_id, answer_text, is_correct, answer_order)
                        VALUES (?, ?, ?, ?)";
                db_query($conn, $sql, "ssii", [$question_id, $true_answer, 1, 1]);
                db_query($conn, $sql, "ssii", [$question_id, $false_answer, 0, 2]);
            } elseif ($question['question_type'] === 'multiple_select') {
                foreach ($question['options'] as $index => $option) {
                    $is_correct = in_array($index, $question['correct_answer_indices']) ? 1 : 0;
                    $sql = "INSERT INTO answers (question_id, answer_text, is_correct, answer_order)
                            VALUES (?, ?, ?, ?)";
                    db_query($conn, $sql, "ssii", [$question_id, $option, $is_correct, $index + 1]);
                }
            }
        }

        db_commit($conn);
        return $quiz_id;

    } catch (Exception $e) {
        db_rollback($conn);
        error_log("Error saving generated quiz: " . $e->getMessage());
        return false;
    }
}
?>
