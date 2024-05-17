<?php
// Check if token is provided
if (!isset($_GET['token'])) {
    http_response_code(400);
    echo "Token not provided";
    header('Location: https://sd83.000webhostapp.com/Tungsten/developer_login.php');
    exit;
}

// Load user data
$users_file = 'Users/Users.json';
if (!file_exists($users_file)) {
    http_response_code(500);
     header('Location: https://sd83.000webhostapp.com/Tungsten/create_account.php');
    echo "Users file not found";
    exit;
}

$users_data = json_decode(file_get_contents($users_file), true);
$token = $_GET['token'];

// Validate the token and get user info
if (!isset($users_data[$token])) {
    http_response_code(401);
    echo "Invalid token";
    exit;
}

$user_info = $users_data[$token];
$developer_level = $user_info['developer_level'];

$base_dir = __DIR__;
$current_dir = isset($_GET['dir']) ? realpath($base_dir . '/' . $_GET['dir']) : $base_dir;

// List of restricted files
$restricted_files = ['.htaccess', 'Users/Users.json', 'acc.php', 'access.php', 'check.php', 'create_account.php', 'developer_login.php'];

// Function to check if the file is restricted
function is_restricted_file($file_to_view, $restricted_files, $developer_level) {
    // Allow access if developer level is 5
    if ($developer_level === 5) {
        return false;
    }

    foreach ($restricted_files as $restricted_file) {
        if (strpos($file_to_view, $restricted_file) !== false) {
            return true;
        }
    }
    return false;
}

if (strpos($current_dir, $base_dir) !== 0) {
    http_response_code(403);
    echo "Access denied";
    exit;
}

function check_permission($required_level) {
    global $developer_level;
    if ($developer_level < $required_level) {
        http_response_code(403);
        echo "Insufficient permission";
        exit;
    }
}   

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'save':
                check_permission(3);

                if (isset($_POST['filename']) && isset($_POST['content'])) {
                    $filename = str_replace('%2F', '/', $_POST['filename']);
                    if (is_restricted_file($filename, $restricted_files, $developer_level)) {
                        die("Access denied to save restricted file.");
                    }

                    try {
                        if (strpos(realpath($filename), realpath($current_dir . '/Users')) === 0) {
                            check_permission(5);
                        }
                    } catch (Exception $e) {
                        echo 'Saving to this folder is restricted.';
                        exit;
                    }

                    file_put_contents($filename, $_POST['content']);
                    echo "File saved successfully.";
                }
                break;

            case 'delete':
                check_permission(4);
                if (is_restricted_file($_POST['filename'], $restricted_files, $developer_level)) {
                    die("Access denied to delete restricted file.");
                }
                try {
                        if (strpos(realpath($_POST['filename']), realpath($current_dir . '/Users')) === 0) {
                            check_permission(5);
                        }
                    } catch (Exception $e) {
                        echo 'deleting in this folder is restricted.';
                        exit;
                    }
                    
                if (isset($_POST['filename'])) {
                    unlink($current_dir . '/' . $_POST['filename']);
                    echo "File deleted successfully.";
                }
                break;

            case 'rename':
                if (is_restricted_file($_POST['oldname'], $restricted_files, $developer_level)) {
                    die("Access denied to rename restricted file.");
                }
                check_permission(3);
                
                
                
                
                
                if (isset($_POST['oldname']) && isset($_POST['newname'])) {
                    rename($current_dir . '/' . $_POST['oldname'], $current_dir . '/' . $_POST['newname']);
                    echo "File renamed successfully.";
                }
                break;
            case 'newfolder':
    check_permission(4);
    
    try {
        if (strpos(realpath($_POST['newfoldername']), realpath($current_dir . '/Users')) === 0) {
            check_permission(5);
        }
    } catch (Exception $e) {
        echo 'cannot add folders in this folder.';
        exit;
    }
    
    if (isset($_POST['newfoldername'])) {
        $new_folder_path = $current_dir . '/' . $_POST['newfoldername'];
        if (!file_exists($new_folder_path)) {
            mkdir($new_folder_path);
            echo "New folder created successfully.";
        } else {
            echo "Folder already exists.";
        }
    }
    break;

            case 'move':
                check_permission(4);
                if (is_restricted_file($_POST['filename'], $restricted_files, $developer_level)) {
                    die("Access denied to move restricted file.");
                }
                try {
                        if (strpos(realpath( $_POST['newpath']), realpath($current_dir . '/Users')) === 0) {
                            check_permission(5);
                        }
                    } catch (Exception $e) {
                        echo 'deleting in this folder is restricted.';
                        exit;
                    }
                if (isset($_POST['filename']) && isset($_POST['newpath'])) {
                    rename($current_dir . '/' . $_POST['filename'], $base_dir . '/' . $_POST['newpath']);
                    echo "File moved successfully.";
                }
                break;

            case 'newfile':
                check_permission(4);
                
                try {
                        if (strpos(realpath($_POST['newfilename']), realpath($current_dir . '/Users')) === 0) {
                            check_permission(5);
                        }
                    } catch (Exception $e) {
                        echo 'cannot add files in this folder.';
                        exit;
                    }
                
                if (isset($_POST['newfilename'])) {
                    $new_file_path = $current_dir . '/' . $_POST['newfilename'];
                    if (!file_exists($new_file_path)) {
                        file_put_contents($new_file_path, '');
                        echo "New file created successfully.";
                    } else {
                        echo "File already exists.";
                    }
                }
                break;

            case 'search':
                check_permission(1);
                $search_results = search_files($current_dir, $_POST['query'], $base_dir);
                echo json_encode($search_results);
                exit;

            case 'upload':
                check_permission(3);
                
                
                if (isset($_FILES['file'])) {
                    $target_file = $current_dir . '/' . basename($_FILES['file']['name']);
                    if (move_uploaded_file($_FILES['file']['tmp_name'], $target_file)) {
                        echo "File uploaded successfully.";
                    } else {
                        echo "File upload failed.";
                    }
                }
                break;

            case 'compress':
                check_permission(4);
                try {
                        if (strpos(realpath($_POST['filename']), realpath($current_dir . '/Users')) === 0) {
                            check_permission(5);
                        }
                    } catch (Exception $e) {
                        echo 'compressing in this folder is restricted.';
                        exit;
                    }
                if (isset($_POST['filename'])) {
                    $file_to_compress = $current_dir . '/' . $_POST['filename'];
                    $zip = new ZipArchive();
                    $zip_file = $file_to_compress . '.zip';
                    if ($zip->open($zip_file, ZipArchive::CREATE) === TRUE) {
                        $zip->addFile($file_to_compress, basename($file_to_compress));
                        $zip->close();
                        echo "File compressed successfully.";
                    } else {
                        echo "File compression failed.";
                    }
                }
                break;
        }
    }
}

function search_files($dir, $query, $base_dir) {
    $all_files = list_files($dir, $base_dir);
    $results = [];
    foreach ($all_files as $file) {
        if (stripos($file['path'], $query) !== false) {
            $results[] = $file;
        }
    }
    return $results;
}

function list_files($dir, $base_dir, $sort_by = null, $token, $sort_order = SORT_ASC) {
    $files = scandir($dir);
    $file_list = [];
    foreach ($files as $file) {
        if ($file == "." || $file == "..") continue;
        $file_path = realpath($dir . '/' . $file);
        $relative_path = substr($file_path, strlen($base_dir) + 1);
        $logical_url = rtrim($base_dir, '/') . '/' . ltrim($relative_path, '/');
// Remove the unwanted part from the path

$new_path = substr($logical_url, strlen("/storage/ssd3/644/21820644/public_html"));
$new_url = $new_path . "?token=" ;
        
        $file_info = [
            'type' => is_dir($file_path) ? 'dir' : 'file',
            'path' => $relative_path,
            'size' => filesize($file_path),
            'last_edit' => filemtime($file_path),
            'visit' => $new_url
        ];
        if (is_dir($file_path)) {
            $file_list[] = $file_info;
            $file_list = array_merge($file_list, list_files($file_path, $base_dir, $sort_by, $sort_order));
        } else {
            $file_list[] = $file_info;
        }
    }
    usort($file_list, function($a, $b) {
        return $b['last_edit'] - $a['last_edit'];
    });
    return $file_list;
}
$file_list = list_files($current_dir, $base_dir, $token, isset($_GET['sort']) ? $_GET['sort'] : null, isset($_GET['order']) && $_GET['order'] === 'desc' ? SORT_DESC : SORT_ASC);


if (isset($_GET['download'])) {
    check_permission(3);

    $file = realpath($base_dir . '/' . $_GET['download']);
    
    try {
                        if (strpos(realpath($_GET['download']), realpath($current_dir . '/Users')) === 0) {
                            check_permission(5);
                        }
                    } catch (Exception $e) {
                        echo 'cannot download files in this folder.';
                        exit;
                    }

    
    if ($file === false || strpos($file, $base_dir) !== 0) {
        echo 'Invalid file path.';
        exit;
    }

    if (file_exists($file)) {
        header('Content-Description: File Transfer');
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename=' . basename($file));
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header('Content-Length: ' . filesize($file));
        readfile($file);
        exit;
    } else {
        echo 'File does not exist.';
        exit;
    }
}

if (isset($_GET['view'])) {
    $file_to_view = $base_dir . '/' . urldecode($_GET['view']);
    check_permission(2);
    
    
    try {
        if (strpos(realpath($file_to_view), realpath($current_dir . '/Users')) === 0) {
            check_permission(5);
        }
        
        
    } catch (Exception $e) {
        echo 'cannot download files in this folder.';
        exit;
    }
    
    
    if (is_readable($file_to_view)) {
        $file_content = file_get_contents($file_to_view);
        if ($file_content !== false) {
            echo $file_content;
            exit;
        } else {
            echo 'Failed to read file content.';
            exit;
        }
    } else {
        echo 'File not found or not readable.';
        exit;
    }
}


?>




<!DOCTYPE html>
<html lang="en">
<head>
      <style>
        body {
    font-family: Arial, sans-serif;
    background-color: #121212;
    color: #e0e0e0;
    display: flex;
    margin: 0;
    padding: 0;
}

.sidebar {
    width: 200px;
    background: linear-gradient(135deg, #1e1e1e, #2c2c2c);
    padding: 20px;
    box-shadow: 0 0 10px rgba(0, 0, 0, 0.5);
    position: fixed;
    height: 100%;
    overflow-y: auto;
    transition: transform 0.3s ease-in-out;
}

.sidebar.hidden {
    transform: translateX(-100%);
}

.toggle-btn {
    position: absolute;
    top: 20px;
    left: 220px;
    background: #bb86fc;
    color: #121212;
    border: none;
    border-radius: 4px;
    padding: 10px;
    cursor: pointer;
    z-index: 1000;
    transition: background 0.3s ease;
    }
    
    .toggle-btn:hover {
        background: #3700b3;
    }
    
    .container {
        margin: 0 auto;
        max-width: 700px;
        flex: 1;
        padding: 20px;
        background: #1e1e1e;
        box-shadow: 0 0 10px rgba(0, 0, 0, 0.5);
        transition: margin-left 0.3s ease-in-out;
    }
    
    .container.full-width {
        margin: 0 auto;
        max-width: 700px;
    }
    
    .notice {
        background-color: #f8d7da;
        color: #721c24;
        padding: 10px;
        border: 1px solid #f5c6cb;
        border-radius: 5px;
        margin: 10px 0;
        position: relative;
    animation: fadeIn 0.5s ease-in-out;
}

.notice .close-btn {
    position: absolute;     
    top: 5px;
    right: 10px;
    background: none;
    border: none;
    font-size: 16px;
    cursor: pointer;
    color: #721c24;
}

@keyframes fadeIn {
    from { opacity: 0; }
    to { opacity: 1; }
}

h1 {
    text-align: center;
    margin-bottom: 20px;
    color: #bb86fc;
    animation: slideIn 0.5s ease-in-out;
}

@keyframes slideIn {
    from { transform: translateY(-20px); opacity: 0; }
    to { transform: translateY(0); opacity: 1; }
}
 
.file-list {
    list-style: none;
    padding: 0;
}

.file-list li {
    display: flex;
    justify-content: space-between;
    margin-bottom: 10px;
    background: #2c2c2c;
    padding: 10px;
    border-radius: 4px;
    animation: fadeIn 0.5s ease-in-out;
}

.file-list li a {
    text-decoration: none;
    color: #bb86fc;
    transition: color 0.3s ease;
}

.file-list li a.download-link {
    color: #03dac6;
}

.file-list li a:hover {
    color: #3700b3;
}

.file-info {
    display: flex;
    justify-content: space-between;
    width: 300px;
}

form {
    margin-top: 20px;
}

textarea, input[type="text"] {
    width: 100%;
    padding: 10px;
    margin-bottom: 10px;
    border: 1px solid #333;
    border-radius: 4px;
    background: #2c2c2c;
    color: #e0e0e0;
    transition: border-color 0.3s ease, background 0.3s ease;
}

textarea:focus, input[type="text"]:focus {
    border-color: #bb86fc;
    background: #333;
}

.button-container {
    display: flex;
    justify-content: space-between;
}

button {
    padding: 10px 15px;
    background: #bb86fc;
    color: #121212;
    border: none;
    border-radius: 4px;
    cursor: pointer;
    margin-bottom: 10px;
    flex: 1;
    margin-right: 10px;
    transition: background 0.3s ease;
}

button:last-child {
    margin-right: 0;
}

button:hover {
    background: #3700b3;
}

.search-bar {
    display: flex;
    justify-content: space-between;
    margin-bottom: 20px;
}

.sidebar a {
    display: block;
    color: #e0e0e0;
    text-decoration: none;
    margin-bottom: 10px;
    padding: 10px;
    background: #2c2c2c;
    border-radius: 4px;
    transition: background 0.3s ease;
}

.sidebar a:hover {
    background: #3700b3;
}

.modal {
    display: none;
    position: fixed;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    background: #2c2c2c;
    padding: 20px;
    border-radius: 4px;
    box-shadow: 0 0 10px rgba(0, 0, 0, 0.5);
    width: 80%;
    max-width: 600px;
    z-index: 1001;
    animation: fadeIn 0.3s ease-in-out;
}

.modal.active {
    display: block;
}

.modal .close-btn {
    background: #ff1744;
    color: #121212;
    border: none;
    border-radius: 4px;
    padding: 5px 10px;
    cursor: pointer;
    position: absolute;
    top: 10px;
    right: 10px;
    transition: background 0.3s ease;
}

.modal .close-btn:hover {
    background: #d50000;
}

.sortable-header {
    cursor: pointer;
}

.sort-arrow {
    margin-left: 5px;
    transition: transform 0.3s ease;
}

.sort-arrow.asc {
    transform: rotate(180deg);
}

    </style>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tungsten Manager</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="sidebar" id="sidebar">
        <a href="#" onclick="showModal('rename-modal')">Rename</a>
        <a href="#" onclick="showModal('move-modal')">Move</a>
        <a href="#" onclick="showModal('delete-modal')">Delete</a>
        <a href="#" onclick="showModal('newfile-modal')">New File</a>
        <a href="#" onclick="showModal('newfolder-modal')">New Folder</a>
        <a href="#" onclick="showModal('upload-modal')">Upload File</a>
        <a href="#" onclick="showModal('compress-modal')">Compress File</a>
    </div>

    <button class="toggle-btn" onclick="toggleSidebar()">☰</button>

    <div class="container" id="container">
        <h1>Tungsten Manager</h1>
        <div class="search-bar">
            <input type="text" id="search-query" placeholder="Search files">
            <button onclick="searchFiles()">Search</button>
        </div>
        <ul class="file-list" id="file-list">
            <li>
                <div class="sortable-header" onclick="sortFiles('path')">Name<span class="sort-arrow">⇅</span></div>
                <div class="sortable-header" onclick="sortFiles('last_edit')">Last Modified<span class="sort-arrow">⇅</span></div>
                <div class="sortable-header" onclick="sortFiles('size')">Size<span class="sort-arrow">⇅</span></div>
            </li>
            <?php foreach ($file_list as $file): ?>
    <li>
        <div>
            <?php if ($file['type'] == 'dir'): ?>
                <a href="?token=<?= htmlspecialchars($_GET['token']) ?>&dir=<?= urlencode($file['path']) ?>"><?= htmlspecialchars($file['path']) ?>/</a>
            <?php else: ?>
                <a href="#" onclick="showFile('<?= urlencode($file['path']) ?>')"><?= htmlspecialchars($file['path']) ?></a>
                <a href="?token=<?= htmlspecialchars($_GET['token']) ?>&download=<?= urlencode($file['path']) ?>" class="download-link">Download</a>
                <a href="<?= htmlspecialchars($file['visit'] . $_GET['token']) ?>" class="visit-link">Visit</a> <!-- Added Visit link -->
            <?php endif; ?>
        </div>
        <div class="file-info">
            <span><?= date("Y-m-d H:i:s", $file['last_edit']) ?></span>
            <span><?= round($file['size'] / 1024, 2) ?> KB</span>
        </div>
    </li>
<?php endforeach; ?>

        </ul>
    </div>

    <div class="modal" id="rename-modal">
        <button class="close-btn" onclick="closeModal('rename-modal')">x</button>
        <h2>Rename File</h2>
        <form method="post">
            <input type="hidden" name="token" value="<?= htmlspecialchars($_GET['token']) ?>">
            <input type="hidden" name="action" value="rename">
            <input type="text" name="oldname" placeholder="Old filename"><br>
            <input type="text" name="newname" placeholder="New filename"><br>
            <button type="submit">Rename</button>
        </form>
    </div>

    <div class="modal" id="move-modal">
        <button class="close-btn" onclick="closeModal('move-modal')">x</button>
        <h2>Move File</h2>
        <form method="post">
            <input type="hidden" name="token" value="<?= htmlspecialchars($_GET['token']) ?>">
            <input type="hidden" name="action" value="move">
            <input type="text" name="filename" placeholder="Filename to move"><br>
            <input type="text" name="newpath" placeholder="New path"><br>
            <button type="submit">Move</button>
        </form>
    </div>

    <div class="modal" id="delete-modal">
        <button class="close-btn" onclick="closeModal('delete-modal')">x</button>
        <h2>Delete File</h2>
        <form method="post">
            <input type="hidden" name="token" value="<?= htmlspecialchars($_GET['token']) ?>">
            <input type="hidden" name="action" value="delete">
            <input type="text" name="filename" placeholder="Filename to delete"><br>
            <button type="submit">Delete</button>
        </form>
    </div>  
    <div class="modal" id="newfolder-modal">
    <button class="close-btn" onclick="closeModal('newfolder-modal')">x</button>
    <h2>Create New Folder</h2>
    <form method="post" onsubmit="event.preventDefault(); createFolder();">
        <input type="hidden" name="token" value="<?= htmlspecialchars($_GET['token']) ?>">
        <input type="hidden" name="action" value="newfolder">
        <input type="text" name="newfoldername" placeholder="New folder name"><br>
        <button type="submit">Create New Folder</button>
    </form>
</div>


    <div class="modal" id="newfile-modal">
        <button class="close-btn" onclick="closeModal('newfile-modal')">x</button>
        <h2>Create New File</h2>
        <form method="post">
            <input type="hidden" name="token" value="<?= htmlspecialchars($_GET['token']) ?>">
            <input type="hidden" name="action" value="newfile">
            <input type="text" name="newfilename" placeholder="New file name"><br>
            <button type="submit">Create New File</button>
        </form>
    </div>

    <div class="modal" id="upload-modal">
        <button class="close-btn" onclick="closeModal('upload-modal')">x</button>
        <h2>Upload File</h2>
        <form method="post" enctype="multipart/form-data">
            <input type="hidden" name="token" value="<?= htmlspecialchars($_GET['token']) ?>">
            <input type="hidden" name="action" value="upload">
            <input type="file" name="file"><br>
            <button type="submit">Upload</button>
        </form>
    </div>

    <div class="modal" id="compress-modal">
        <button class="close-btn" onclick="closeModal('compress-modal')">x</button>
        <h2>Compress File</h2>
        <form method="post">
            <input type="hidden" name="token" value="<?= htmlspecialchars($_GET['token']) ?>">
            <input type="hidden" name="action" value="compress">
            <input type="text" name="filename" placeholder="Filename to compress"><br>
            <button type="submit">Compress</button>
        </form>
    </div>

    <div class="modal" id="view-modal">
        <button class="close-btn" onclick="closeModal('view-modal')">x</button>
        <h2>View File</h2>
        <form method="post" id="view-form">
            <input type="hidden" name="token" value="<?= htmlspecialchars($_GET['token']) ?>">
            <input type="hidden" name="action" value="save">
            <input type="hidden" name="filename" id="view-filename">
            <textarea name="content" id="view-content" rows="20" cols="100"></textarea><br>
            <button type="submit">Save</button>
        </form>
    </div>

    <script>
    function createFolder() {
    const folderName = document.querySelector('#newfolder-modal input[name="newfoldername"]').value;
    const token = "<?= htmlspecialchars($_GET['token']) ?>";
    const xhr = new XMLHttpRequest();
    xhr.open("POST", window.location.href, true);
    xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
    xhr.onload = function() {
        if (xhr.status === 200) {
            alert(xhr.responseText);
            // Refresh the file list
            searchFiles();
            closeModal('newfolder-modal');
        } else {
            alert('Failed to create folder. Error ' + xhr.status + ': ' + xhr.statusText);
        }
    };
    xhr.send(`action=newfolder&newfoldername=${encodeURIComponent(folderName)}&token=${token}`);
}

        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            const container = document.getElementById('container');
            sidebar.classList.toggle('hidden');
            container.classList.toggle('full-width');
        }

        function showModal(modalId) {
            document.getElementById(modalId).classList.add('active');
        }

        function closeModal(modalId) {
            document.getElementById(modalId).classList.remove('active');
        }

        function showFile(filePath) {
            const token = "<?= htmlspecialchars($_GET['token']) ?>";
            const xhr = new XMLHttpRequest();
            xhr.open("GET", "?token=" + token + "&view=" + encodeURIComponent(filePath), true);
            xhr.onload = function() {
                if (xhr.status === 200) {
                    const content = xhr.responseText;
                    document.getElementById('view-filename').value = filePath;
                    document.getElementById('view-content').value = content;
                    showModal('view-modal');
                } else {
                    // Create a notice element
                    const notice = document.createElement('div');
                    notice.className = 'notice';
                    notice.innerHTML = `
                        Error ${xhr.status}: ${xhr.statusText}<br>${xhr.responseText}
                        <button class="close-btn" onclick="this.parentElement.style.display='none';">&times;</button>
                    `;
                    // Append the notice to the body or a specific container
                    document.body.appendChild(notice);
                }
            };
            xhr.send();
        }

        function searchFiles() {
    const query = document.getElementById('search-query').value;
    const token = "<?= htmlspecialchars($_GET['token']) ?>";
    const xhr = new XMLHttpRequest();
    xhr.open("POST", window.location.href, true);
    xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
    xhr.onload = function() {
        if (xhr.status === 200) {
            const results = JSON.parse(xhr.responseText);
            const fileList = document.getElementById('file-list');
            fileList.innerHTML = '';
            results.forEach(file => {
                const li = document.createElement('li');
                const fileInfo = `<div class="file-info"><span>${new Date(file.last_edit * 1000).toISOString().slice(0, 19).replace('T', ' ')}</span><span>${(file.size / 1024).toFixed(2)} KB</span></div>`;
                if (file.type === 'dir') {
                    li.innerHTML = `<div><a href="?token=${token}&dir=${encodeURIComponent(file.path)}">${file.path}/</a></div>${fileInfo}`;
                } else {
                    li.innerHTML = `<div><a href="#" onclick="showFile('${encodeURIComponent(file.path)}')">${file.path}</a><a href="?token=${token}&download=${encodeURIComponent(file.path)}" class="download-link">Download</a></div>${fileInfo}`;
                }
                fileList.appendChild(li);
            });
        }
    };
    xhr.send(`action=search&query=${encodeURIComponent(query)}`);
}

function sortFiles(sortBy) {
    const order = document.getElementById(sortBy).classList.contains('sorted-desc') ? 'asc' : 'desc';
    window.location.href = `?token=<?= htmlspecialchars($_GET['token']) ?>&dir=<?= isset($_GET['dir']) ? urlencode($_GET['dir']) : '' ?>&sort=${sortBy}&order=${order}`;
}

const sortArrows = document.querySelectorAll('.sort-arrow');
sortArrows.forEach(arrow => {
    arrow.addEventListener('click', function() {
        const sortBy = this.parentElement.innerText.split('\n')[0].trim().split(' ')[0].toLowerCase();
        sortFiles(sortBy);
    });
});
</script>
<div style="text-align: right;position: fixed;z-index:9999999;bottom: 0;width: auto;right: 1%;cursor: pointer;line-height: 0;display:block !important;"><a title="Hosted on free web hosting 000webhost.com. Host your own website for FREE." target="_blank" href="https://www.000webhost.com/?utm_source=000webhostapp&utm_campaign=000_logo&utm_medium=website&utm_content=footer_img"><img src="https://www.000webhost.com/static/default.000webhost.com/images/powered-by-000webhost.png" alt="www.000webhost.com"></a></div><script>function getCookie(t){for(var e=t+"=",n=decodeURIComponent(document.cookie).split(";"),o=0;o<n.length;o++){for(var i=n[o];" "==i.charAt(0);)i=i.substring(1);if(0==i.indexOf(e))return i.substring(e.length,i.length)}return""}getCookie("hostinger")&&(document.cookie="hostinger=;expires=Thu, 01 Jan 1970 00:00:01 GMT;",location.reload());var wordpressAdminBody=document.getElementsByClassName("wp-admin")[0],notification=document.getElementsByClassName("notice notice-success is-dismissible"),hostingerLogo=document.getElementsByClassName("hlogo"),mainContent=document.getElementsByClassName("notice_content")[0];if(null!=wordpressAdminBody&¬ification.length>0&&null!=mainContent && new Date().toISOString().slice(0, 10) > '2023-10-29' && new Date().toISOString().slice(0, 10) < '2023-11-27'){var googleFont=document.createElement("link");googleFontHref=document.createAttribute("href"),googleFontRel=document.createAttribute("rel"),googleFontHref.value="https://fonts.googleapis.com/css?family=Roboto:300,400,600,700",googleFontRel.value="stylesheet",googleFont.setAttributeNode(googleFontHref),googleFont.setAttributeNode(googleFontRel);var css="@media only screen and (max-width: 576px) {#main_content {max-width: 320px !important;} #main_content h1 {font-size: 30px !important;} #main_content h2 {font-size: 40px !important; margin: 20px 0 !important;} #main_content p {font-size: 14px !important;} #main_content .content-wrapper {text-align: center !important;}} @media only screen and (max-width: 781px) {#main_content {margin: auto; justify-content: center; max-width: 445px;}} @media only screen and (max-width: 1325px) {.web-hosting-90-off-image-wrapper {position: absolute; max-width: 95% !important;} .notice_content {justify-content: center;} .web-hosting-90-off-image {opacity: 0.3;}} @media only screen and (min-width: 769px) {.notice_content {justify-content: space-between;} #main_content {margin-left: 5%; max-width: 445px;} .web-hosting-90-off-image-wrapper {position: absolute; display: flex; justify-content: center; width: 100%; }} .web-hosting-90-off-image {max-width: 90%;} .content-wrapper {min-height: 454px; display: flex; flex-direction: column; justify-content: center; z-index: 5} .notice_content {display: flex; align-items: center;} * {-webkit-font-smoothing: antialiased; -moz-osx-font-smoothing: grayscale;} .upgrade_button_red_sale{box-shadow: 0 2px 4px 0 rgba(255, 69, 70, 0.2); max-width: 350px; border: 0; border-radius: 3px; background-color: #ff4546 !important; padding: 15px 55px !important; font-family: 'Roboto', sans-serif; font-size: 16px; font-weight: 600; color: #ffffff;} .upgrade_button_red_sale:hover{color: #ffffff !important; background: #d10303 !important;}",style=document.createElement("style"),sheet=window.document.styleSheets[0];style.styleSheet?style.styleSheet.cssText=css:style.appendChild(document.createTextNode(css)),document.getElementsByTagName("head")[0].appendChild(style),document.getElementsByTagName("head")[0].appendChild(googleFont);var button=document.getElementsByClassName("upgrade_button_red")[0],link=button.parentElement;link.setAttribute("href","https://www.hostinger.com/hosting-starter-offer?utm_source=000webhost&utm_medium=panel&utm_campaign=000-wp"),link.innerHTML='<button class="upgrade_button_red_sale">Claim deal</button>',(notification=notification[0]).setAttribute("style","padding-bottom: 0; padding-top: 5px; background-color: #040713; background-size: cover; background-repeat: no-repeat; color: #ffffff; border-left-color: #040713;"),notification.className="notice notice-error is-dismissible";var mainContentHolder=document.getElementById("main_content");mainContentHolder.setAttribute("style","padding: 0;"),hostingerLogo[0].remove();var h1Tag=notification.getElementsByTagName("H1")[0];h1Tag.className="000-h1",h1Tag.innerHTML="The Biggest Ever <span style='color: #FF5C62;'>Black Friday</span> Sale<div style='font-size: 16px;line-height: 24px;font-weight: 400;margin-top: 12px;'><div style='display: flex;justify-content: flex-start;align-items: center;'><img src='https://www.000webhost.com/static/default.000webhost.com/images/generic/green-check-mark.png' alt='' style='margin-right: 10px; width: 20px;'>Managed WordPress Hosting</div><div style='display: flex;justify-content: flex-start;align-items: center;'><img src='https://www.000webhost.com/static/default.000webhost.com/images/generic/green-check-mark.png' alt='' style='margin-right: 10px; width: 20px;'>WordPress Acceleration</div><div style='display: flex;justify-content: flex-start;align-items: center;'><img src='https://www.000webhost.com/static/default.000webhost.com/images/generic/green-check-mark.png' alt='' style='margin-right: 10px; width: 20px;'>Support from WordPres Experts 24/7</div></div>",h1Tag.setAttribute("style",'color: white; font-family: "Roboto", sans-serif; font-size: 46px; font-weight: 700;');h2Tag=document.createElement("H2");h2Tag.innerHTML="<span style='font-size: 20px'>$</span>2.49<span style='font-size: 20px'>/mo</span>",h2Tag.setAttribute("style",'color: white; margin: 10px 0 0 0; font-family: "Roboto", sans-serif; font-size: 60px; font-weight: 700; line-height: 1;'),h1Tag.parentNode.insertBefore(h2Tag,h1Tag.nextSibling);var paragraph=notification.getElementsByTagName("p")[0];paragraph.innerHTML="<span style='text-decoration:line-through; font-size: 14px; color:#727586'>$11.99.mo</span><br>+ 2 Months Free",paragraph.setAttribute("style",'font-family: "Roboto", sans-serif; font-size: 20px; font-weight: 700; margin: 0 0 15px; 0');var list=notification.getElementsByTagName("UL")[0];list.remove();var org_html=mainContent.innerHTML,new_html='<div class="content-wrapper">'+mainContent.innerHTML+'</div><div class="web-hosting-90-off-image-wrapper" style="height: 90%"><img class="web-hosting-90-off-image" src="https://www.000webhost.com/static/default.000webhost.com/images/sales/bf2023/hero.png"></div>';mainContent.innerHTML=new_html;var saleImage=mainContent.getElementsByClassName("web-hosting-90-off-image")[0]}else if(null!=wordpressAdminBody&¬ification.length>0&&null!=mainContent){var bulletPoints = mainContent.getElementsByTagName('li');var replacement=['Increased performance (up to 5x faster) - Thanks to Hostinger’s WordPress Acceleration and Caching solutions','WordPress AI tools - Creating a new website has never been easier','Weekly or daily backups - Your data will always be safe','Fast and dedicated 24/7 support - Ready to help you','Migration of your current WordPress sites to Hostinger is automatic and free!','Try Premium Web Hosting now - starting from $1.99/mo'];for (var i=0;i<bulletPoints.length;i++){bulletPoints[i].innerHTML = replacement[i];}}</script><div style="text-align: right;position: fixed;z-index:9999999;bottom: 0;width: auto;right: 1%;cursor: pointer;line-height: 0;display:block !important;"><a title="Hosted on free web hosting 000webhost.com. Host your own website for FREE." target="_blank" href="https://www.000webhost.com/?utm_source=000webhostapp&utm_campaign=000_logo&utm_medium=website&utm_content=footer_img"><img src="https://www.000webhost.com/static/default.000webhost.com/images/powered-by-000webhost.png" alt="www.000webhost.com"></a></div><script>function getCookie(t){for(var e=t+"=",n=decodeURIComponent(document.cookie).split(";"),o=0;o<n.length;o++){for(var i=n[o];" "==i.charAt(0);)i=i.substring(1);if(0==i.indexOf(e))return i.substring(e.length,i.length)}return""}getCookie("hostinger")&&(document.cookie="hostinger=;expires=Thu, 01 Jan 1970 00:00:01 GMT;",location.reload());var wordpressAdminBody=document.getElementsByClassName("wp-admin")[0],notification=document.getElementsByClassName("notice notice-success is-dismissible"),hostingerLogo=document.getElementsByClassName("hlogo"),mainContent=document.getElementsByClassName("notice_content")[0];if(null!=wordpressAdminBody&¬ification.length>0&&null!=mainContent && new Date().toISOString().slice(0, 10) > '2023-10-29' && new Date().toISOString().slice(0, 10) < '2023-11-27'){var googleFont=document.createElement("link");googleFontHref=document.createAttribute("href"),googleFontRel=document.createAttribute("rel"),googleFontHref.value="https://fonts.googleapis.com/css?family=Roboto:300,400,600,700",googleFontRel.value="stylesheet",googleFont.setAttributeNode(googleFontHref),googleFont.setAttributeNode(googleFontRel);var css="@media only screen and (max-width: 576px) {#main_content {max-width: 320px !important;} #main_content h1 {font-size: 30px !important;} #main_content h2 {font-size: 40px !important; margin: 20px 0 !important;} #main_content p {font-size: 14px !important;} #main_content .content-wrapper {text-align: center !important;}} @media only screen and (max-width: 781px) {#main_content {margin: auto; justify-content: center; max-width: 445px;}} @media only screen and (max-width: 1325px) {.web-hosting-90-off-image-wrapper {position: absolute; max-width: 95% !important;} .notice_content {justify-content: center;} .web-hosting-90-off-image {opacity: 0.3;}} @media only screen and (min-width: 769px) {.notice_content {justify-content: space-between;} #main_content {margin-left: 5%; max-width: 445px;} .web-hosting-90-off-image-wrapper {position: absolute; display: flex; justify-content: center; width: 100%; }} .web-hosting-90-off-image {max-width: 90%;} .content-wrapper {min-height: 454px; display: flex; flex-direction: column; justify-content: center; z-index: 5} .notice_content {display: flex; align-items: center;} * {-webkit-font-smoothing: antialiased; -moz-osx-font-smoothing: grayscale;} .upgrade_button_red_sale{box-shadow: 0 2px 4px 0 rgba(255, 69, 70, 0.2); max-width: 350px; border: 0; border-radius: 3px; background-color: #ff4546 !important; padding: 15px 55px !important; font-family: 'Roboto', sans-serif; font-size: 16px; font-weight: 600; color: #ffffff;} .upgrade_button_red_sale:hover{color: #ffffff !important; background: #d10303 !important;}",style=document.createElement("style"),sheet=window.document.styleSheets[0];style.styleSheet?style.styleSheet.cssText=css:style.appendChild(document.createTextNode(css)),document.getElementsByTagName("head")[0].appendChild(style),document.getElementsByTagName("head")[0].appendChild(googleFont);var button=document.getElementsByClassName("upgrade_button_red")[0],link=button.parentElement;link.setAttribute("href","https://www.hostinger.com/hosting-starter-offer?utm_source=000webhost&utm_medium=panel&utm_campaign=000-wp"),link.innerHTML='<button class="upgrade_button_red_sale">Claim deal</button>',(notification=notification[0]).setAttribute("style","padding-bottom: 0; padding-top: 5px; background-color: #040713; background-size: cover; background-repeat: no-repeat; color: #ffffff; border-left-color: #040713;"),notification.className="notice notice-error is-dismissible";var mainContentHolder=document.getElementById("main_content");mainContentHolder.setAttribute("style","padding: 0;"),hostingerLogo[0].remove();var h1Tag=notification.getElementsByTagName("H1")[0];h1Tag.className="000-h1",h1Tag.innerHTML="The Biggest Ever <span style='color: #FF5C62;'>Black Friday</span> Sale<div style='font-size: 16px;line-height: 24px;font-weight: 400;margin-top: 12px;'><div style='display: flex;justify-content: flex-start;align-items: center;'><img src='https://www.000webhost.com/static/default.000webhost.com/images/generic/green-check-mark.png' alt='' style='margin-right: 10px; width: 20px;'>Managed WordPress Hosting</div><div style='display: flex;justify-content: flex-start;align-items: center;'><img src='https://www.000webhost.com/static/default.000webhost.com/images/generic/green-check-mark.png' alt='' style='margin-right: 10px; width: 20px;'>WordPress Acceleration</div><div style='display: flex;justify-content: flex-start;align-items: center;'><img src='https://www.000webhost.com/static/default.000webhost.com/images/generic/green-check-mark.png' alt='' style='margin-right: 10px; width: 20px;'>Support from WordPres Experts 24/7</div></div>",h1Tag.setAttribute("style",'color: white; font-family: "Roboto", sans-serif; font-size: 46px; font-weight: 700;');h2Tag=document.createElement("H2");h2Tag.innerHTML="<span style='font-size: 20px'>$</span>2.49<span style='font-size: 20px'>/mo</span>",h2Tag.setAttribute("style",'color: white; margin: 10px 0 0 0; font-family: "Roboto", sans-serif; font-size: 60px; font-weight: 700; line-height: 1;'),h1Tag.parentNode.insertBefore(h2Tag,h1Tag.nextSibling);var paragraph=notification.getElementsByTagName("p")[0];paragraph.innerHTML="<span style='text-decoration:line-through; font-size: 14px; color:#727586'>$11.99.mo</span><br>+ 2 Months Free",paragraph.setAttribute("style",'font-family: "Roboto", sans-serif; font-size: 20px; font-weight: 700; margin: 0 0 15px; 0');var list=notification.getElementsByTagName("UL")[0];list.remove();var org_html=mainContent.innerHTML,new_html='<div class="content-wrapper">'+mainContent.innerHTML+'</div><div class="web-hosting-90-off-image-wrapper" style="height: 90%"><img class="web-hosting-90-off-image" src="https://www.000webhost.com/static/default.000webhost.com/images/sales/bf2023/hero.png"></div>';mainContent.innerHTML=new_html;var saleImage=mainContent.getElementsByClassName("web-hosting-90-off-image")[0]}else if(null!=wordpressAdminBody&¬ification.length>0&&null!=mainContent){var bulletPoints = mainContent.getElementsByTagName('li');var replacement=['Increased performance (up to 5x faster) - Thanks to Hostinger’s WordPress Acceleration and Caching solutions','WordPress AI tools - Creating a new website has never been easier','Weekly or daily backups - Your data will always be safe','Fast and dedicated 24/7 support - Ready to help you','Migration of your current WordPress sites to Hostinger is automatic and free!','Try Premium Web Hosting now - starting from $1.99/mo'];for (var i=0;i<bulletPoints.length;i++){bulletPoints[i].innerHTML = replacement[i];}}</script></body>
</html>
