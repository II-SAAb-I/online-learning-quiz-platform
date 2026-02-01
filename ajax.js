function searchCourses(query, category = '') {
    fetch(`../api/search-courses.php?q=${encodeURIComponent(query)}&category=${encodeURIComponent(category)}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                displaySearchResults(data.courses);
            }
        })
        .catch(error => console.error('Search failed:', error));
}

function displaySearchResults(courses) {
    const resultsContainer = document.getElementById('search-results');
    if (!resultsContainer) return;
    
    if (courses.length === 0) {
        resultsContainer.innerHTML = '<p>No courses found</p>';
        return;
    }
    
    let html = '';
    courses.forEach(course => {
        html += `
            <div class="course-item">
                <h4>${course.title}</h4>
                <p>${course.short_description}</p>
                <span class="badge">${course.category}</span>
            </div>
        `;
    });
    
    resultsContainer.innerHTML = html;
}

function updateCourseProgress(courseId) {
    const formData = new FormData();
    formData.append('course_id', courseId);
    
    fetch('../api/update-progress.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            console.log('Progress updated:', data.progress + '%');
            
            const progressBar = document.querySelector('.progress-bar');
            if (progressBar) {
                progressBar.style.width = data.progress + '%';
                progressBar.textContent = data.progress.toFixed(1) + '%';
            }
        }
    })
    .catch(error => console.error('Progress update failed:', error));
}

document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('course-search');
    if (searchInput) {
        let searchTimeout;
        searchInput.addEventListener('input', function() {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => {
                searchCourses(this.value);
            }, 500);
        });
    }
});