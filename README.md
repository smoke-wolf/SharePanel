# SharePanel

SharePanel is a PHP-based file management system designed to offer fine-grained access control for developers based on their permission levels. This application supports various file operations such as viewing, uploading, creating, renaming, deleting, moving, and compressing, with restrictions based on user permissions and directories.

## Features

- **User Authentication:** Users are authenticated via a token passed in the URL.
- **Permission Levels:** Different operations are allowed based on the user's developer level.
- **File Operations:** Users can view, upload, create, rename, delete, move, and compress files and folders.
- **Directory Restrictions:** Access to certain files and directories can be restricted based on user permissions.
- **Search Functionality:** Users can search for files within the current directory.
- **UI Elements:** A responsive and interactive UI for performing file operations.

## Installation

1. **Clone or Download the Repository:**
    - If cloned via Git:
      ```bash
      git clone https://github.com/smoke-wolf/SharePanel.git
      ```
    - If downloaded as a .zip, unpack the zip and upload all the files.

2. **Upload the Files:**
    - Upload the files from the `SharePanel` directory to your server.

3. **Configure the Application:**
    - Navigate to `https://your/application.com/developer_login.php`.
    - Log in with the default credentials:
      - Username: `ADMIN`
      - Password: `Password`
    - Create a new account by going to `https://your/application.com/create_account.php`.
    - Edit the `Users/Users.json` file:
      - Increase the `"developer_level"` to 5.
      - Change `"allowed_dirs": "/Your_App"` to `"allowed_dirs": "/"`.
    - Delete the ADMIN account and log in with the newly created account.

4. **Customize Your Application:**
    - Change restricted files on line 38.
    - Change your application name on lines 668 and 685.
    - Add your login redirect on line 8.
    - Add your create_account redirect on line 16.

5. **Add Team Members:**
    - Get your team members to create an account at `https://your/application.com/create_account.php`.
    - Assign permissions in `Users/Users.json`:
      - Level 1: Basic file viewing and searching.
      - Level 2: Advanced viewing options.
      - Level 3: Saving and uploading files.
      - Level 4: Creating new files, folders, and moving files.
      - Level 5: Full access, including restricted areas and file operations.
    - For specific directory access, modify `"allowed_dirs": "/Your_App"` to `"allowed_dirs": "/Your_App/Subdir"`.

## File Operations

- **Viewing Files:** Users can view files by clicking on the file name. Access is restricted based on the user's `allowed_dirs`.
- **Uploading Files:** Accessible to users with level 3 or higher. Restricted files and directories are not uploadable.
- **Creating Files and Folders:** Users with level 4 or higher can create new files and folders. Restricted directories cannot have new files or folders created.
- **Renaming, Moving, and Deleting:** Available to users with level 4 or higher. Special permissions (level 5) are required for operations in restricted directories.
- **Compressing Files:** Users with level 4 or higher can compress files into zip archives. Restricted files cannot be compressed without proper permissions.
- **Searching Files:** Available to users with level 1 or higher. Searches for files within the current directory.
- **Sidebar Operations:** Rename, Move, Delete, New File, New Folder, Upload File, Compress File options.

## Security Considerations

- Ensure the `Users/Users.json` file is not accessible via the web. Use an `.htaccess` file to cover this.
- Validate and sanitize all user inputs to prevent security vulnerabilities.
- Use HTTPS to protect token and data transmission.

## Troubleshooting

- **Token not provided:** Ensure the token is included in the URL.
- **Users file not found:** Ensure `Users/Users.json` exists and is correctly formatted.
- **Invalid token:** Verify the token against the `Users/Users.json` data.
- **Insufficient permission:** Ensure the user has the correct `developer_level` for the desired operation.

## Contributing

Contributions are welcome! Please submit pull requests or open issues for any bugs or feature requests.

## License

This project is licensed under the terms of the MIT license. See the LICENSE file for details.
