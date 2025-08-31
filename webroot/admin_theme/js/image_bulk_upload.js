Dropzone.autoDiscover = false; // Recommended

document.addEventListener('DOMContentLoaded', function() {
    const dropzoneForm = document.getElementById('imageUploadDropzone');
    if (!dropzoneForm) {
        console.error('Dropzone form #imageUploadDropzone not found.');
        return;
    }

    const uploadUrl = dropzoneForm.dataset.uploadUrl;
    const deleteUrl = dropzoneForm.dataset.deleteUrl;
    const csrfToken = dropzoneForm.dataset.csrfToken;
    const notificationsDiv = document.getElementById('upload-notifications');
    const refreshButton = document.getElementById('refreshPageButton');

    let successfulUploadsCount = 0;
    let failedUploadsCount = 0;

    const myDropzone = new Dropzone("#imageUploadDropzone", {
        paramName: "image",
        maxFilesize: 20, // MB
        maxFiles: 50,
        acceptedFiles: `image/*,.zip,.tar,.tar.gz,.tgz`,
        addRemoveLinks: true, // Enable remove links
        headers: {
            'X-CSRF-Token': csrfToken,
            'Accept': 'application/json' // Crucial for CakePHP content negotiation
        },
        dictDefaultMessage: "Drop files here or click to upload",
        dictRemoveFile: "Remove file",
        dictFileTooBig: "File is too big ({{filesize}}MB). Max filesize: {{maxFilesize}}MB.",
        dictInvalidFileType: "You can't upload files of this type.",
        dictResponseError: "Server responded with {{statusCode}} code.",
        dictCancelUpload: "Cancel upload",
        dictCancelUploadConfirmation: "Are you sure you want to cancel this upload?",
        dictMaxFilesExceeded: "You cannot upload any more files.",

        init: function() {
            this.on("addedfile", function(file) {
                // Reset notifications on new batch if it's the first file of a new potential batch
                if (this.getQueuedFiles().length === 1 && this.getUploadingFiles().length === 0 && this.files.filter(f => f.status === Dropzone.SUCCESS || f.status === Dropzone.ERROR).length === 0) {
                    successfulUploadsCount = 0;
                    failedUploadsCount = 0;
                    if (notificationsDiv) notificationsDiv.innerHTML = '';
                    if (refreshButton) refreshButton.style.display = 'none';
                }
            });

            this.on("success", function(file, response) {
                file.previewElement.classList.add("dz-success");
                const successMessage = file.previewElement.querySelector("[data-dz-successmessage]"); // Dropzone 6+
                if(successMessage) successMessage.textContent = response.message || "Uploaded successfully";

                if (response.image && response.image.id) {
                    file.serverId = response.image.id; // Store server ID for deletion
                }
                successfulUploadsCount++;
                // Optional: remove file preview after a delay
                // setTimeout(() => { this.removeFile(file); }, 5000);
            });

            this.on("error", function(file, errorMessage, xhr) {
                file.previewElement.classList.add("dz-error");
                const errorDisplay = file.previewElement.querySelector("[data-dz-errormessage]");
                let displayMessage = "Upload failed.";

                if (typeof errorMessage === "object" && errorMessage !== null) { // Our JSON response
                    displayMessage = errorMessage.message || "An error occurred.";
                    if (errorMessage.errors) {
                        let validationMsgs = [];
                        for (const field in errorMessage.errors) {
                            validationMsgs.push(Object.values(errorMessage.errors[field]).join(', '));
                        }
                        displayMessage += "<br><small>" + validationMsgs.join('<br>') + "</small>";
                    }
                } else if (typeof errorMessage === "string") {
                    displayMessage = errorMessage;
                } else if (xhr && xhr.statusText) {
                     displayMessage = `Error ${xhr.status}: ${xhr.statusText}`;
                }

                if (errorDisplay) {
                    errorDisplay.innerHTML = displayMessage;
                }
                console.error("Upload error:", file.name, errorMessage, xhr);
                failedUploadsCount++;
            });

            this.on("queuecomplete", function() {
                if (notificationsDiv) {
                    let message = `Upload queue finished. Successful: ${successfulUploadsCount}, Failed: ${failedUploadsCount}.`;
                    notificationsDiv.className = 'alert ' + (failedUploadsCount > 0 ? 'alert-warning' : 'alert-success');
                    notificationsDiv.textContent = message;
                }
                if (refreshButton) {
                    refreshButton.style.display = 'inline-block';
                    refreshButton.onclick = function() { location.reload(); };
                }
            });

            this.on("removedfile", function(file) {
                if (file.serverId && (file.status === "success" || file.status === Dropzone.SUCCESS)) {
                    fetch(`${deleteUrl}/${file.serverId}`, {
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-Token': csrfToken,
                            'Accept': 'application/json'
                        }
                    })
                    .then(response => {
                        if (!response.ok) {
                            // Try to parse error from server if possible
                            return response.json().then(err => { throw err; });
                        }
                        return response.json();
                    })
                    .then(data => {
                        if (data.success) {
                            console.log('File deleted from server:', data.message);
                            successfulUploadsCount = Math.max(0, successfulUploadsCount - 1); // Decrement if it was a success
                        } else {
                            console.error('Failed to delete file from server:', data.message);
                            alert('Could not delete file from server: ' + data.message);
                            // Optionally, re-add the file to the Dropzone UI to indicate it wasn't deleted.
                            // This can be complex; for now, an alert is simpler.
                        }
                    })
                    .catch(error => {
                        let errorMsg = 'Error sending delete request.';
                        if (error && error.message) {
                            errorMsg = error.message;
                        }
                        console.error('Error deleting file:', error);
                        alert('Failed to delete file: ' + errorMsg);
                    })
                    .finally(() => {
                        // Update counts if queuecomplete hasn't run yet or if we want dynamic updates
                        if (this.getQueuedFiles().length === 0 && this.getUploadingFiles().length === 0) {
                             if (notificationsDiv) {
                                let message = `Summary: Successful: ${successfulUploadsCount}, Failed: ${failedUploadsCount}.`;
                                notificationsDiv.textContent = message;
                             }
                        }
                    });
                } else if (file.status === "error" || file.status === Dropzone.ERROR) {
                    failedUploadsCount = Math.max(0, failedUploadsCount - 1); // Decrement if it was a failure
                     if (this.getQueuedFiles().length === 0 && this.getUploadingFiles().length === 0) {
                         if (notificationsDiv) {
                            let message = `Summary: Successful: ${successfulUploadsCount}, Failed: ${failedUploadsCount}.`;
                            notificationsDiv.textContent = message;
                         }
                     }
                }
            });
        }
    });
});