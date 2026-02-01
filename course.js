function enrollInCourse(courseId) {
    if (!confirm('Enroll in this course?')) {
        return false;
    }
    
    window.location.href = `enroll.php?id=${courseId}`;
}

function toggleCourseDetails(courseId) {
    const details = document.getElementById('course-details-' + courseId);
    if (details) {
        details.style.display = details.style.display === 'none' ? 'block' : 'none';
    }
}

function filterCourses() {
    const category = document.getElementById('category-filter').value;
    const difficulty = document.getElementById('difficulty-filter').value;
    
    window.location.href = `courses.php?category=${category}&difficulty=${difficulty}`;
}

document.addEventListener('DOMContentLoaded', function() {
    const enrollButtons = document.querySelectorAll('.enroll-btn');
    enrollButtons.forEach(button => {
        button.addEventListener('click', function() {
            const courseId = this.dataset.courseId;
            enrollInCourse(courseId);
        });
    });
});