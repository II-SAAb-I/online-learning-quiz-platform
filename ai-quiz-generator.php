<?php
require_once '../config/config.php';
require_once '../config/database.php';
require_once '../includes/functions.php';
require_once '../includes/session.php';
require_once '../includes/auth.php';
require_once '../includes/openai.php';

require_instructor();

$user_id = get_user_id();
$course_id = intval($_GET['course_id'] ?? 0);

if (!$course_id) {
    set_message('error', 'Invalid course');
    redirect('courses.php');
}

if (!owns_resource($conn, 'courses', $course_id, 'created_by', $user_id)) {
    set_message('error', 'Access denied');
    redirect('courses.php');
}

// Get course details
$sql = "SELECT * FROM courses WHERE id = ?";
$result = db_query($conn, $sql, "i", [$course_id]);
$course = db_fetch_assoc($result);

if (!$course) {
    set_message('error', 'Course not found');
    redirect('courses.php');
}

$page_title = 'AI Quiz Generator';
$step = $_GET['step'] ?? 'input';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        set_message('error', 'Invalid security token');
    } else {
        if ($step === 'input') {
            // Process lesson details and generate recommendations
            $lesson_topic = sanitize($_POST['lesson_topic'] ?? '');
            $subjects = sanitize($_POST['subjects'] ?? '');
            
            if (empty($lesson_topic)) {
                set_message('error', 'Lesson topic is required');
            } else {
                // Generate quiz recommendations
                $recommendations = generate_quiz_recommendations($lesson_topic, $subjects);
                
                if ($recommendations) {
                    $_SESSION['ai_quiz_data'] = [
                        'lesson_topic' => $lesson_topic,
                        'subjects' => $subjects,
                        'recommendations' => $recommendations
                    ];
                    redirect('ai-quiz-generator.php?course_id=' . $course_id . '&step=recommendations');
                } else {
                    set_message('error', 'Failed to generate quiz recommendations. Please try again.');
                }
            }
        } elseif ($step === 'recommendations') {
            // Process selected quizzes
            $selected_quizzes = $_POST['selected_quizzes'] ?? [];
            
            if (empty($selected_quizzes)) {
                set_message('error', 'Please select at least one quiz to generate');
            } else {
                $ai_data = $_SESSION['ai_quiz_data'] ?? [];
                
                if (empty($ai_data)) {
                    set_message('error', 'Session expired. Please start over.');
                    redirect('ai-quiz-generator.php?course_id=' . $course_id);
                }
                
                $generated_quizzes = [];
                $errors = [];
                
                foreach ($selected_quizzes as $index) {
                    $quiz_data = $ai_data['recommendations'][$index] ?? null;
                    
                    if ($quiz_data) {
                        // Generate questions for this quiz
                        $questions = generate_quiz_questions(
                            $quiz_data['title'],
                            $quiz_data['description'],
                            $quiz_data['difficulty'],
                            $quiz_data['num_questions'],
                            $quiz_data['question_types']
                        );
                        
                        if ($questions) {
                            // Save quiz to database
                            $quiz_id = save_generated_quiz($course_id, $quiz_data, $questions);
                            
                            if ($quiz_id) {
                                $generated_quizzes[] = [
                                    'title' => $quiz_data['title'],
                                    'id' => $quiz_id
                                ];
                            } else {
                                $errors[] = "Failed to save quiz: " . $quiz_data['title'];
                            }
                        } else {
                            $errors[] = "Failed to generate questions for quiz: " . $quiz_data['title'];
                        }
                    }
                }
                
                if (!empty($generated_quizzes)) {
                    set_message('success', 'Successfully generated ' . count($generated_quizzes) . ' quiz(es)!');
                    unset($_SESSION['ai_quiz_data']);
                    redirect('quizzes.php?course_id=' . $course_id);
                } else {
                    set_message('error', 'Failed to generate quizzes: ' . implode(', ', $errors));
                }
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?> - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="<?php echo ASSETS_URL; ?>/css/style.css">
    <link rel="stylesheet" href="<?php echo ASSETS_URL; ?>/css/dashboard.css">
</head>
<body>
    <div class="dashboard-wrapper">
        <aside class="sidebar">
            <div class="sidebar-header">
                <h2>👨‍🏫 Instructor</h2>
            </div>
            <nav class="sidebar-nav">
                <a href="dashboard.php" class="nav-item">📊 Dashboard</a>
                <a href="courses.php" class="nav-item active">📚 My Courses</a>
                <a href="course-create.php" class="nav-item">➕ Create Course</a>
                <a href="students.php" class="nav-item">👥 Students</a>
                <a href="analytics.php" class="nav-item">📈 Analytics</a>
                <a href="profile.php" class="nav-item">👤 Profile</a>
                <a href="../logout.php" class="nav-item">🚪 Logout</a>
            </nav>
        </aside>
        
        <main class="main-content">
            <div class="content-header">
                <h1><?php echo $page_title; ?></h1>
                <div>
                    <a href="quizzes.php?course_id=<?php echo $course_id; ?>" class="btn btn-secondary">← Back to Quizzes</a>
                </div>
            </div>
            
            <?php display_message(); ?>
            
            <div class="card">
                <div class="card-body">
                    <?php if ($step === 'input'): ?>
                        <h3>Step 1: Tell us about your lesson</h3>
                        <p>Provide details about the lesson topic and related subjects to generate personalized quiz recommendations.</p>
                        
                        <form method="POST" action="">
                            <?php echo csrf_field(); ?>
                            
                            <div class="form-group">
                                <label for="lesson_topic">Lesson Topic *</label>
                                <input type="text" id="lesson_topic" name="lesson_topic" class="form-control" required 
                                       placeholder="e.g., Introduction to Algebra, World War II, Photosynthesis">
                            </div>
                            
                            <div class="form-group">
                                <label for="subjects">Related Subjects/Key Concepts</label>
                                <textarea id="subjects" name="subjects" class="form-control" rows="3" 
                                          placeholder="e.g., Variables, equations, linear functions&#10;Causes, major battles, consequences&#10;Light reaction, Calvin cycle, cellular respiration"></textarea>
                                <small class="form-text">Separate multiple subjects with commas or new lines</small>
                            </div>
                            
                            <div class="mt-4">
                                <button type="submit" class="btn btn-primary">Generate Quiz Recommendations</button>
                                <a href="quizzes.php?course_id=<?php echo $course_id; ?>" class="btn btn-secondary">Cancel</a>
                            </div>
                        </form>
                        
                    <?php elseif ($step === 'recommendations'): ?>
                        <?php
                        $ai_data = $_SESSION['ai_quiz_data'] ?? [];
                        if (empty($ai_data)) {
                            echo '<p class="text-center text-muted">Session expired. Please start over.</p>';
                        } else {
                        ?>
                            <h3>Step 2: Choose Quizzes to Generate</h3>
                            <p>Based on your lesson topic "<strong><?php echo htmlspecialchars($ai_data['lesson_topic']); ?></strong>", here are our AI-generated quiz recommendations:</p>
                            
                            <form method="POST" action="">
                                <?php echo csrf_field(); ?>
                                
                                <div class="quiz-recommendations">
                                    <?php foreach ($ai_data['recommendations'] as $index => $quiz): ?>
                                        <div class="quiz-recommendation-card">
                                            <div class="form-check">
                                                <input type="checkbox" id="quiz_<?php echo $index; ?>" name="selected_quizzes[]" value="<?php echo $index; ?>" class="form-check-input">
                                                <label for="quiz_<?php echo $index; ?>" class="form-check-label">
                                                    <div class="quiz-info">
                                                        <h4><?php echo htmlspecialchars($quiz['title']); ?></h4>
                                                        <p><?php echo htmlspecialchars($quiz['description']); ?></p>
                                                        <div class="quiz-meta">
                                                            <span class="badge badge-<?php echo $quiz['difficulty'] === 'beginner' ? 'success' : ($quiz['difficulty'] === 'intermediate' ? 'warning' : 'danger'); ?>">
                                                                <?php echo ucfirst($quiz['difficulty']); ?>
                                                            </span>
                                                            <span class="text-muted"><?php echo $quiz['num_questions']; ?> questions</span>
                                                            <span class="text-muted"><?php echo implode(', ', array_map('ucfirst', $quiz['question_types'])); ?></span>
                                                        </div>
                                                    </div>
                                                </label>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                                
                                <div class="mt-4">
                                    <button type="submit" class="btn btn-primary">Generate Selected Quizzes</button>
                                    <a href="ai-quiz-generator.php?course_id=<?php echo $course_id; ?>" class="btn btn-secondary">← Back</a>
                                </div>
                            </form>
                        <?php } ?>
                    <?php endif; ?>
                </div>
            </div>
        </main>
    </div>
</body>
</html>
