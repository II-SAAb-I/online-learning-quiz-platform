<?php
require_once '../config/config.php';
require_once '../config/database.php';
require_once '../includes/functions.php';
require_once '../includes/session.php';
require_once '../includes/auth.php';

require_admin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        set_message('error', 'Invalid security token');
    } else {
        $action = sanitize($_POST['action'] ?? '');
        
        if ($action === 'add') {
            $category = sanitize($_POST['category'] ?? '');
            
            if (empty($category)) {
                set_message('error', 'Category name is required');
            } else {
                $check_sql = "SELECT COUNT(*) as count FROM courses WHERE category = ?";
                $check_result = db_query($conn, $check_sql, "s", [$category]);
                $check = db_fetch_assoc($check_result);
                
                if ($check['count'] > 0) {
                    set_message('error', 'Category already exists');
                } else {
                    $sql = "INSERT INTO courses (title, short_description, long_description, category, created_by, is_published) 
                            VALUES ('__CATEGORY_PLACEHOLDER__', '', '', ?, 1, 0)";
                    db_query($conn, $sql, "s", [$category]);
                    
                    $delete_sql = "DELETE FROM courses WHERE title = '__CATEGORY_PLACEHOLDER__'";
                    db_query($conn, $delete_sql);
                    
                    set_message('success', 'Category added successfully');
                }
            }
        } elseif ($action === 'rename') {
            $old_category = sanitize($_POST['old_category'] ?? '');
            $new_category = sanitize($_POST['new_category'] ?? '');
            
            if (empty($old_category) || empty($new_category)) {
                set_message('error', 'Both category names are required');
            } else {
                $sql = "UPDATE courses SET category = ? WHERE category = ?";
                $result = db_query($conn, $sql, "ss", [$new_category, $old_category]);
                
                if ($result) {
                    set_message('success', 'Category renamed successfully');
                } else {
                    set_message('error', 'Failed to rename category');
                }
            }
        } elseif ($action === 'delete') {
            $category = sanitize($_POST['category'] ?? '');
            
            if (empty($category)) {
                set_message('error', 'Category name is required');
            } else {
                $count_sql = "SELECT COUNT(*) as count FROM courses WHERE category = ?";
                $count_result = db_query($conn, $count_sql, "s", [$category]);
                $count = db_fetch_assoc($count_result);
                
                if ($count['count'] > 0) {
                    set_message('error', 'Cannot delete category with existing courses. Move courses first.');
                } else {
                    set_message('info', 'Category has no courses and can be removed by renaming all courses from this category');
                }
            }
        }
    }
}

$categories_sql = "SELECT category, COUNT(*) as course_count 
                   FROM courses 
                   WHERE category IS NOT NULL AND category != ''
                   GROUP BY category 
                   ORDER BY category";
$categories = db_fetch_all(db_query($conn, $categories_sql));

$page_title = 'Manage Categories';
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
                <h2>🎓 Admin Panel</h2>
            </div>
            <nav class="sidebar-nav">
                <a href="dashboard.php" class="nav-item">
                    📊 Dashboard
                </a>
                <a href="users.php" class="nav-item">
                    👥 Users
                </a>
                <a href="courses.php" class="nav-item">
                    📚 Courses
                </a>
                <a href="categories.php" class="nav-item active">
                    🏷️ Categories
                </a>
                <a href="analytics.php" class="nav-item">
                    📈 Analytics
                </a>
                <a href="reports.php" class="nav-item">
                    📄 Reports
                </a>
                <a href="settings.php" class="nav-item">
                    ⚙️ Settings
                </a>
                <a href="../logout.php" class="nav-item">
                    🚪 Logout
                </a>
            </nav>
        </aside>
        
        <main class="main-content">
            <div class="content-header">
                <h1><?php echo $page_title; ?></h1>
            </div>
            
            <?php display_message(); ?>
            
            <div class="row">
                <div class="col-6">
                    <div class="card mb-4">
                        <div class="card-header">
                            <h3>Add New Category</h3>
                        </div>
                        <div class="card-body">
                            <form method="POST" action="">
                                <?php echo csrf_field(); ?>
                                <input type="hidden" name="action" value="add">
                                
                                <div class="form-group">
                                    <label for="category">Category Name</label>
                                    <input type="text" id="category" name="category" class="form-control" required placeholder="e.g., Web Development">
                                </div>
                                
                                <button type="submit" class="btn btn-primary">Add Category</button>
                            </form>
                        </div>
                    </div>
                    
                    <div class="card">
                        <div class="card-header">
                            <h3>Rename Category</h3>
                        </div>
                        <div class="card-body">
                            <form method="POST" action="">
                                <?php echo csrf_field(); ?>
                                <input type="hidden" name="action" value="rename">
                                
                                <div class="form-group">
                                    <label for="old_category">Current Category</label>
                                    <select id="old_category" name="old_category" class="form-control" required>
                                        <option value="">Select category...</option>
                                        <?php foreach ($categories as $cat): ?>
                                        <option value="<?php echo htmlspecialchars($cat['category']); ?>">
                                            <?php echo htmlspecialchars($cat['category']); ?>
                                        </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                
                                <div class="form-group">
                                    <label for="new_category">New Category Name</label>
                                    <input type="text" id="new_category" name="new_category" class="form-control" required>
                                </div>
                                
                                <button type="submit" class="btn btn-warning">Rename Category</button>
                            </form>
                        </div>
                    </div>
                </div>
                
                <div class="col-6">
                    <div class="card">
                        <div class="card-header">
                            <h3>All Categories</h3>
                        </div>
                        <div class="card-body">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Category Name</th>
                                        <th>Courses</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($categories as $cat): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($cat['category']); ?></td>
                                        <td>
                                            <span class="badge badge-primary"><?php echo number_format($cat['course_count']); ?></span>
                                        </td>
                                        <td>
                                            <a href="courses.php?category=<?php echo urlencode($cat['category']); ?>" class="btn btn-sm btn-primary">
                                                View Courses
                                            </a>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                    
                                    <?php if (empty($categories)): ?>
                                    <tr>
                                        <td colspan="3" class="text-center">No categories found</td>
                                    </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</body>
</html>