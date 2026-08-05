document.getElementById('photoInput').addEventListener('change', async function(e) {
  const file = e.target.files[0];
  if (!file) return;

  // Process & compress client-side
  const compressedBlob = await compressImage(file, 1200, 0.8);
  
  // Display preview
  const preview = document.getElementById('imagePreview');
  preview.src = URL.createObjectURL(compressedBlob);
  document.getElementById('previewContainer').classList.remove('hidden');

  // Store processed blob on the file input element for form submission
  e.target.dataset.processedBlob = true;
  e.target.processedBlob = compressedBlob;
});

// Client-side resizing canvas helper
function compressImage(file, maxWidth, quality) {
  return new Promise((resolve) => {
    const reader = new FileReader();
    reader.readAsDataURL(file);
    reader.onload = (event) => {
      const img = new Image();
      img.src = event.target.result;
      img.onload = () => {
        const canvas = document.createElement('canvas');
        let width = img.width;
        let height = img.height;

        if (width > maxWidth) {
          height = Math.round((height * maxWidth) / width);
          width = maxWidth;
        }

        canvas.width = width;
        canvas.height = height;

        const ctx = canvas.getContext('2d');
        ctx.drawImage(img, 0, 0, width, height);

        // Convert canvas to Blob (JPG or WebP format)
        canvas.toBlob((blob) => resolve(blob), 'image/jpeg', quality);
      };
    };
  });
}

// Upload Form Handler
document.getElementById('uploadForm').addEventListener('submit', async function(e) {
  e.preventDefault();
  
  const photoInput = document.getElementById('photoInput');
  const formData = new FormData();

  if (photoInput.processedBlob) {
    // Generate unique filename matching your current format
    const fileName = `${Date.now()}_${Math.random().toString(36).substring(2, 8)}.jpg`;
    formData.append('photo', photoInput.processedBlob, fileName);
  }

  try {
    const response = await fetch('api/upload_photo.php', {
      method: 'POST',
      body: formData
    });

    const result = await response.json();
    if (result.status === 'success') {
      alert(`Photo uploaded successfully: ${result.filename}`);
    } else {
      alert(`Upload failed: ${result.message}`);
    }
  } catch (error) {
    console.error('Upload error:', error);
  }
});