<?php
session_start();

// Check if the user is not logged in, redirect to the login page
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

// Include the connection.php file to fetch database details
require_once('connection.php');

// Get the username from the session
$username = $_SESSION['username'];

// Retrieve user data from the database using the username
$sql = "SELECT * FROM users WHERE username = '$username'";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    $user = $result->fetch_assoc();

    // Get the user ID
    $userId = $user['id'];

    // Define the path to the user's directory
    $userDirectory = "contents/users/{$username}/";

    // Check if the directory exists
    if (is_dir($userDirectory)) {
        // Open the directory
        if ($dirHandle = opendir($userDirectory)) {
            // Start HTML page
            echo '<html>
                    <head>
                        <title>User Folders</title>
                         <link rel="stylesheet" href="css/style2.css">
                     
                    </head>
                    <body>
                        <h2>Username: ' . $username . '</h2>
                        
                        <button onclick="openCreateFolderModal()">Create Folder</button>
                        <button onclick="openUploadModal()">Upload</button>
                        <ul>';

          // Loop through each entry in the directory
while (($entry = readdir($dirHandle)) !== false) {
    // Skip entries that are not directories or are special entries (like "." and "..")
    if (is_dir($userDirectory . $entry) && $entry != "." && $entry != "..") {
        // Display the folder name and a "View Files" button
        echo '<li data-foldername="' . $entry . '">' . $entry . ' <button onclick="viewFiles(\'' . $username . '\', \'' . $entry . '\')">View Files</button></li>';
    }
}


            // Close the directory handle
            closedir($dirHandle);

            // Finish HTML page
            echo '</ul>

                  <!-- Modal for creating folder -->
                  <div id="createFolderModal" class="modal" style="display:none">
                    <div class="modal-content">
                      <span onclick="closeCreateFolderModal()" class="close" title="Close Modal">&times;</span>
                      <h3>Create Folder</h3>
                      <label for="folderName">Folder Name:</label>
                      <input type="text" id="folderName" name="folderName" required>
                      <button onclick="createFolder()">Create</button>
                      <br>
                      <button onclick="closeCreateFolderModal()">Close</button>
                    </div>
                  </div>

                  <script>
                      function viewFiles(username, folder) {
                          window.location.href = "gallery.php?username=" + encodeURIComponent(username) + "&folder=" + encodeURIComponent(folder);
                      }

                      function openCreateFolderModal() {
    var createFolderModal = document.getElementById("createFolderModal");
    createFolderModal.style.display = "block";
    setTimeout(function () {
        createFolderModal.classList.add("fade-in");
    }, 10); // Delay for smooth transition
}

                      function closeCreateFolderModal() {
    var createFolderModal = document.getElementById("createFolderModal");
    createFolderModal.classList.remove("fade-in");
    setTimeout(function () {
        createFolderModal.style.display = "none";
    }, 500); // Ensure the modal is hidden after the fade-out effect
}

                      function createFolder() {
                          var folderName = document.getElementById("folderName").value;

                          // Check if folderName is provided
                          if (folderName.trim() === "") {
                              alert("Please provide a folder name.");
                              return;
                          }

                          // Use AJAX to send a request to create_folder.php
                          var xhr = new XMLHttpRequest();
                          xhr.onreadystatechange = function () {
                              if (xhr.readyState === 4) {
                                  if (xhr.status === 200) {
                                      // Folder created successfully
                                      alert("Folder created successfully.");
                                      // After successful creation, refresh the page
                                      location.reload();
                                  } else {
                                      // Handle error
                                      alert("Error creating folder: " + xhr.responseText);
                                  }
                              }
                          };

                          xhr.open("POST", "create_folder.php", true);
                          xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
                          xhr.send("username=" + encodeURIComponent("' . $username . '") + "&folderName=" + encodeURIComponent(folderName));
                      }
 

                    
                  </script>


                  

                    <!-- Modal for uploading files -->
          <div id="uploadModal" class="modal" style="display:none">
            <div class="modal-content">
              <span onclick="closeUploadModal()" class="close" title="Close Modal">&times;</span>
              <h3>Upload Files</h3>
              <label for="folderSelect">Select Folder:</label>
              <select id="folderSelect" name="folderSelect">
                <!-- Populate this dropdown with existing folders -->
              </select>
              <input type="file" id="fileInput" name="fileInput" multiple accept="image/*,video/*">
              <button onclick="uploadFiles()">Upload</button>
              <div id="progressContainer" style="display:none">
                <p>Files Uploading in Progress:</p>
                <div id="progressBarContainer">
                  <span id="progressBar"></span>
                </div>
              </div>
              <br>
              <button onclick="closeUploadModal()">Close</button>
            </div>
          </div>
<script>

                    function openUploadModal() {
    // Populate the folder dropdown with existing folders
    var folderSelect = document.getElementById("folderSelect");
    folderSelect.innerHTML = "";
    var folders = document.querySelectorAll("ul li[data-foldername]");

    console.log("Number of folders:", folders.length);  // Add this line for debugging

    if (folders.length === 0) {
        alert("No folders found.");
        return;
    }

    for (var i = 0; i < folders.length; i++) {
        var folderName = folders[i].getAttribute("data-foldername");
        console.log("Folder Name:", folderName); // Add this line for debugging
        if (folderName.trim() !== "") { // Exclude folders without a name
            var option = document.createElement("option");
            option.value = folderName;
            option.textContent = folderName;
            folderSelect.appendChild(option);
        }
    }

    document.getElementById("uploadModal").style.display = "block";
}


                      function closeUploadModal() {
                          document.getElementById("uploadModal").style.display = "none";
                          document.getElementById("progressContainer").style.display = "none";
                          document.getElementById("progressBar").style.width = "0%";
                      }


                     function uploadFiles() {
                          var folderSelect = document.getElementById("folderSelect");
                          var selectedFolder = folderSelect.value;
                          var fileInput = document.getElementById("fileInput");
                          var files = fileInput.files;

                          // Check if a folder is selected
                          if (selectedFolder.trim() === "") {
                              alert("Please select a folder.");
                              return;
                          }

                          // Check if files are selected
                          if (files.length === 0) {
                              alert("Please select at least one file.");
                              return;
                          }

                          // Use FormData to send files to upload.php
                          var formData = new FormData();
                          formData.append("username", "' . $username . '");
                          formData.append("folderName", selectedFolder);
                          for (var i = 0; i < files.length; i++) {
                              formData.append("files[]", files[i]);
                          }

                          // Use AJAX to send a request to upload.php
                          var xhr = new XMLHttpRequest();
                          xhr.upload.onprogress = function (e) {
                              if (e.lengthComputable) {
                                  var percentComplete = (e.loaded / e.total) * 100;
                                  document.getElementById("progressContainer").style.display = "block";
                                  document.getElementById("progressBar").style.width = percentComplete + "%";
                              }
                          };

                          xhr.onreadystatechange = function () {
                              if (xhr.readyState === 4) {
                                  if (xhr.status === 200) {
                                      // Files uploaded successfully
                                      alert("Files uploaded successfully.");
                                      // After successful upload, close the modal and refresh the page
                                      closeUploadModal();
                                      location.reload();
                                  } else {
                                      // Handle error
                                      alert("Error uploading files: " + xhr.responseText);
                                  }
                              }
                          };

                          xhr.open("POST", "upload.php", true);
                          xhr.send(formData);
                      }




</script>


                  </body>
                  </html>';
        } else {
            echo "Error opening the directory.";
        }
    } else {
        echo "User directory does not exist.";
    }
} else {
    echo "User not found in the database.";
}
$conn->close();
?>
