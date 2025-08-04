<template>
  <div class="image-upload-container">
    <div 
      class="upload-zone" 
      :class="{ 'drag-over': isDragOver, 'has-file': selectedFile }"
      @drop.prevent="handleDrop"
      @dragover.prevent="isDragOver = true"
      @dragleave.prevent="isDragOver = false"
      @click="triggerFileInput"
    >
      <div v-if="!selectedFile" class="upload-content">
        <i class="fas fa-camera fa-3x text-primary mb-3"></i>
        <h5 class="mb-3">Upload Chess Board Photo</h5>
        <p class="text-muted mb-3">
          Take a photo of your chess board and we'll recognize the pieces
        </p>
        <p class="small text-muted">
          Drag & drop an image here or <span class="text-primary fw-bold">click to browse</span>
        </p>
        <p class="small text-muted">
          Supports JPG, PNG, WEBP (max 10MB)
        </p>
      </div>
      
      <div v-else class="file-preview">
        <img :src="previewUrl" alt="Chess board preview" class="preview-image">
        <div class="file-info mt-3">
          <h6 class="mb-2">{{ selectedFile.name }}</h6>
          <p class="small text-muted mb-3">{{ formatFileSize(selectedFile.size) }}</p>
          <div class="d-flex gap-2">
            <button 
              class="btn btn-primary" 
              @click.stop="processImage"
              :disabled="isProcessing"
            >
              <i class="fas fa-search me-2"></i>
              {{ isProcessing ? 'Analyzing...' : 'Analyze Board' }}
            </button>
            <button 
              class="btn btn-outline-secondary" 
              @click.stop="clearFile"
            >
              <i class="fas fa-times me-2"></i>Remove
            </button>
          </div>
        </div>
      </div>
      
      <div v-if="isProcessing" class="processing-overlay">
        <div class="spinner-border text-primary" role="status">
          <span class="visually-hidden">Loading...</span>
        </div>
        <p class="mt-2 mb-0">Recognizing chess pieces...</p>
      </div>
    </div>
    
    <input 
      ref="fileInput"
      type="file" 
      accept="image/jpeg,image/jpg,image/png,image/webp"
      @change="handleFileSelect"
      style="display: none"
    >
    
    <div v-if="error" class="alert alert-danger mt-3">
      <i class="fas fa-exclamation-triangle me-2"></i>{{ error }}
    </div>
    
    <div class="upload-tips mt-4">
      <h6 class="text-muted mb-3">
        <i class="fas fa-lightbulb me-2"></i>Tips for best results:
      </h6>
      <ul class="small text-muted">
        <li>Take photo from directly above the board</li>
        <li>Ensure good lighting and minimal shadows</li>
        <li>Include the entire board in the frame</li>
        <li>Avoid reflections and glare</li>
      </ul>
    </div>
  </div>
</template>

<script>
export default {
  name: 'ImageUpload',
  emits: ['board-recognized'],
  data() {
    return {
      selectedFile: null,
      previewUrl: null,
      isDragOver: false,
      isProcessing: false,
      error: null
    }
  },
  methods: {
    triggerFileInput() {
      if (!this.selectedFile) {
        this.$refs.fileInput.click();
      }
    },
    
    handleFileSelect(event) {
      const file = event.target.files[0];
      if (file) {
        this.processFile(file);
      }
    },
    
    handleDrop(event) {
      this.isDragOver = false;
      const files = event.dataTransfer.files;
      if (files.length > 0) {
        this.processFile(files[0]);
      }
    },
    
    processFile(file) {
      this.error = null;
      
      if (!this.isValidFile(file)) {
        return;
      }
      
      this.selectedFile = file;
      this.createPreview(file);
    },
    
    isValidFile(file) {
      const validTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/webp'];
      const maxSize = 10 * 1024 * 1024; // 10MB
      
      if (!validTypes.includes(file.type)) {
        this.error = 'Please select a valid image file (JPG, PNG, or WEBP)';
        return false;
      }
      
      if (file.size > maxSize) {
        this.error = 'File size must be less than 10MB';
        return false;
      }
      
      return true;
    },
    
    createPreview(file) {
      const reader = new FileReader();
      reader.onload = (e) => {
        this.previewUrl = e.target.result;
      };
      reader.readAsDataURL(file);
    },
    
    async processImage() {
      if (!this.selectedFile) return;
      
      this.isProcessing = true;
      this.error = null;
      
      try {
        const formData = new FormData();
        formData.append('board_image', this.selectedFile);
        
        const response = await fetch('/api/recognize-board', {
          method: 'POST',
          body: formData,
          headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
          }
        });
        
        if (!response.ok) {
          throw new Error('Failed to process image');
        }
        
        const result = await response.json();
        this.$emit('board-recognized', result);
        
      } catch (error) {
        this.error = 'Failed to analyze the chess board. Please try again.';
        console.error('Board recognition error:', error);
      } finally {
        this.isProcessing = false;
      }
    },
    
    clearFile() {
      this.selectedFile = null;
      this.previewUrl = null;
      this.error = null;
      this.$refs.fileInput.value = '';
      // Clear any previous recognition results
      this.$emit('board-recognized', null);
    },
    
    formatFileSize(bytes) {
      if (bytes === 0) return '0 Bytes';
      const k = 1024;
      const sizes = ['Bytes', 'KB', 'MB', 'GB'];
      const i = Math.floor(Math.log(bytes) / Math.log(k));
      return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
    }
  }
}
</script>

<style scoped>
.image-upload-container {
  max-width: 600px;
  margin: 0 auto;
}

.upload-zone {
  border: 3px dashed #dee2e6;
  border-radius: 15px;
  padding: 40px 20px;
  text-align: center;
  cursor: pointer;
  transition: all 0.3s ease;
  background: #f8f9fa;
  position: relative;
  min-height: 300px;
  display: flex;
  flex-direction: column;
  justify-content: center;
}

.upload-zone:hover {
  border-color: #0d6efd;
  background: #f0f7ff;
}

.upload-zone.drag-over {
  border-color: #0d6efd;
  background: #e3f2fd;
  transform: scale(1.02);
}

.upload-zone.has-file {
  cursor: default;
  border-color: #198754;
  background: #f8fff9;
}

.upload-zone.has-file:hover {
  background: #f8fff9;
  transform: none;
}

.upload-content {
  display: flex;
  flex-direction: column;
  align-items: center;
}

.file-preview {
  display: flex;
  flex-direction: column;
  align-items: center;
}

.preview-image {
  max-width: 100%;
  max-height: 200px;
  border-radius: 10px;
  box-shadow: 0 4px 8px rgba(0,0,0,0.1);
}

.file-info {
  text-align: center;
}

.processing-overlay {
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(255,255,255,0.9);
  display: flex;
  flex-direction: column;
  justify-content: center;
  align-items: center;
  border-radius: 15px;
}

.upload-tips {
  background: #f8f9fa;
  border-radius: 10px;
  padding: 20px;
}

.upload-tips ul {
  margin-bottom: 0;
  padding-left: 20px;
}

.upload-tips li {
  margin-bottom: 8px;
}

@media (max-width: 576px) {
  .upload-zone {
    padding: 30px 15px;
    min-height: 250px;
  }
  
  .preview-image {
    max-height: 150px;
  }
}
</style>