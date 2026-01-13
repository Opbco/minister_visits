$(document).ready(function () {
    
    var selectedFiles = [];


    $('input[id$="_aOptions"]').on('change', function(e) {
        const checkbox = $(this);
        const domainsDiv = $('.specialite-group-options');
        
        if (checkbox.is(':checked')) {
            domainsDiv.removeClass('hidden');
        } else {
            domainsDiv.addClass('hidden');
        }
    });
            
    // Handle file input change
    $('input[id*="_picture_file"]').on('change', function(e) {
        const files = Array.from(e.target.files);
        
        // Add new files to selected files array
        selectedFiles = [];
        
        files.forEach(file => {
            if (file.type.startsWith('image/')) {
                selectedFiles.push(file);
            }
        });
        
        displayPreviews();
    });
    
    // Function to display image previews
    function displayPreviews() {
        const container = $('[id*="picture_file_help"]');
        container.empty();
        
        if (selectedFiles.length === 0) {
            container.html('<div class="no-files">No images selected. Choose images to see preview.</div>');
            return;
        }
                               
        selectedFiles.forEach((file, index) => {
            const reader = new FileReader();
            
            reader.onload = function(e) {
                const previewItem = $(`
                    <div class="preview-item" data-index="${index}">
                        <img src="${e.target.result}" alt="Preview" class="preview-image">
                        <button class="remove-btn" onclick="removeImage(${index})">&times;</button>
                        <div class="preview-info">
                            <div class="file-name">${file.name}</div>
                            <div class="file-size">${formatFileSize(file.size)}</div>
                        </div>
                    </div>
                `);
                
                container.append(previewItem);
            };
            
            reader.readAsDataURL(file);
        });
    }

    // Function to remove image from preview
    window.removeImage = function(index) {
        selectedFiles.splice(index, 1);
        displayPreviews();
        
        // Update file input (create new FileList)
        const dt = new DataTransfer();
        selectedFiles.forEach(file => {
            dt.items.add(file);
        });
        
        $('input[id*="_picture_file"]')[0].files = dt.files;
    };
    
    // Function to format file size
    function formatFileSize(bytes) {
        if (bytes === 0) return '0 Bytes';
        const k = 1024;
        const sizes = ['Bytes', 'KB', 'MB', 'GB'];
        const i = Math.floor(Math.log(bytes) / Math.log(k));
        return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
    }
});
  