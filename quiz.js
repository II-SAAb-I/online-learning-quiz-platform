class QuizTimer {
    constructor(timeLimit, elementId) {
        this.timeRemaining = timeLimit * 60;
        this.element = document.getElementById(elementId);
        this.interval = null;
    }

    start() {
        this.updateDisplay();
        this.interval = setInterval(() => {
            this.timeRemaining--;
            this.updateDisplay();
            
            if (this.timeRemaining <= 60) {
                this.element.style.background = 'var(--error-color)';
            }
            
            if (this.timeRemaining <= 0) {
                this.stop();
                this.onTimeUp();
            }
        }, 1000);
    }

    stop() {
        if (this.interval) {
            clearInterval(this.interval);
        }
    }

    updateDisplay() {
        const minutes = Math.floor(this.timeRemaining / 60);
        const seconds = this.timeRemaining % 60;
        this.element.textContent = 
            minutes.toString().padStart(2, '0') + ':' + 
            seconds.toString().padStart(2, '0');
    }

    onTimeUp() {
        alert('Time is up! The quiz will be submitted automatically.');
        document.getElementById('quizForm').submit();
    }
}

function autoSaveQuiz(quizId) {
    const form = document.getElementById('quizForm');
    const formData = new FormData(form);
    
    fetch('../api/auto-save-quiz.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        console.log('Quiz progress saved');
    })
    .catch(error => {
        console.error('Auto-save failed:', error);
    });
}

document.addEventListener('DOMContentLoaded', function() {
    const quizForm = document.getElementById('quizForm');
    
    if (quizForm) {
        setInterval(() => {
            const quizId = document.querySelector('input[name="quiz_id"]').value;
            autoSaveQuiz(quizId);
        }, 30000);
        
        quizForm.addEventListener('submit', function(e) {
            if (!confirm('Are you sure you want to submit your answers?')) {
                e.preventDefault();
            }
        });
    }
});