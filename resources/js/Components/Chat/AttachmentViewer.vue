<template>
  <div class="attachment-viewer-overlay" @click.self="$emit('close')">
    <div class="attachment-viewer-container">
      <button @click="$emit('close')" class="close-button">
        <i class="fas fa-times"></i>
      </button>
      
      <div class="attachment-content">
        <img v-if="isImage(src)" :src="src" :alt="src" class="image-preview">
        <video v-else-if="isVideo(src)" controls class="video-preview">
          <source :src="src" type="video/mp4">
          Your browser does not support the video tag.
        </video>
        <div v-else class="document-viewer">
          <i :class="getFileIcon(src)"></i>
          <a :href="src" target="_blank" class="download-button">
            <i class="fas fa-download"></i> Download
          </a>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
defineProps({
  src: String
});

function isImage(filePath) {
  return /\.(jpg|jpeg|png|gif|bmp|webp)$/i.test(filePath);
}

function isVideo(filePath) {
  return /\.(mp4|webm|ogg)$/i.test(filePath);
}

function getFileIcon(filePath) {
  const extension = filePath.split('.').pop().toLowerCase();
  switch(extension) {
    case 'pdf': return 'fas fa-file-pdf pdf-icon';
    case 'doc': case 'docx': return 'fas fa-file-word word-icon';
    case 'xls': case 'xlsx': return 'fas fa-file-excel excel-icon';
    case 'ppt': case 'pptx': return 'fas fa-file-powerpoint ppt-icon';
    case 'zip': case 'rar': case '7z': return 'fas fa-file-archive archive-icon';
    case 'txt': return 'fas fa-file-alt text-icon';
    default: return 'fas fa-file file-icon';
  }
}
</script>

<style scoped>
.attachment-viewer-overlay {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background-color: rgba(0, 0, 0, 0.8);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 1000;
}

.attachment-viewer-container {
  position: relative;
  max-width: 90vw;
  max-height: 90vh;
}

.close-button {
  position: absolute;
  top: -40px;
  right: 0;
  background: none;
  border: none;
  color: white;
  font-size: 24px;
  cursor: pointer;
}

.attachment-content {
  background-color: white;
  border-radius: 8px;
  overflow: hidden;
}

.image-preview {
  max-width: 90vw;
  max-height: 90vh;
  object-fit: contain;
}

.video-preview {
  max-width: 90vw;
  max-height: 90vh;
}

.document-viewer {
  padding: 40px;
  text-align: center;
}

.document-viewer i {
  font-size: 60px;
  margin-bottom: 20px;
  display: block;
}

.pdf-icon {
  color: #f44336;
}

.word-icon {
  color: #2196f3;
}

.excel-icon {
  color: #4caf50;
}

.ppt-icon {
  color: #ff9800;
}

.archive-icon {
  color: #795548;
}

.text-icon {
  color: #607d8b;
}

.download-button {
  background-color: #3f51b5;
  color: white;
  padding: 10px 20px;
  border-radius: 4px;
  text-decoration: none;
  display: inline-flex;
  align-items: center;
  gap: 8px;
}

.download-button:hover {
  background-color: #303f9f;
}
</style>