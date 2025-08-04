<template>
  <div>
    <!-- Navigation Bar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
      <div class="container">
        <a class="navbar-brand fw-bold" href="#">
          <i class="fas fa-chess me-2"></i>ChessBot
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
          <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
          <div class="navbar-nav ms-auto">
            <a class="nav-link" href="#features">Features</a>
            <a class="nav-link" href="#upload">Upload Board</a>
            <button class="btn btn-outline-light ms-2 mt-2 mt-lg-0" @click="scrollToUpload">
              <i class="fas fa-camera me-2"></i>Analyze Board
            </button>
          </div>
        </div>
      </div>
    </nav>

    <!-- Hero Section -->
    <section class="hero-section bg-dark text-white py-4 py-md-5">
      <div class="container">
        <div class="row align-items-center min-vh-mobile">
          <div class="col-lg-6 text-center text-lg-start mb-4 mb-lg-0">
            <h1 class="display-6 display-md-4 fw-bold mb-3 mb-md-4">Analyze Real Chess Positions</h1>
            <p class="lead mb-3 mb-md-4 fs-6 fs-md-5">
              Take a photo of your chess board and let our AI recognize the pieces and position. 
              Get instant analysis and improve your game with advanced insights.
            </p>
            <div class="d-grid d-sm-flex gap-2 gap-sm-3 justify-content-center justify-content-lg-start">
              <button class="btn btn-warning btn-lg fw-bold" @click="scrollToUpload">
                <i class="fas fa-camera me-2"></i>Upload Board Photo
              </button>
              <button class="btn btn-outline-light btn-lg" @click="scrollToFeatures">
                <i class="fas fa-info-circle me-2"></i>Learn More
              </button>
            </div>
          </div>
          <div class="col-lg-6 text-center">
            <div class="chess-board-preview">
              <div class="chess-pieces-display">
                <div class="chess-piece-row">
                  <i class="fas fa-chess-rook text-light me-1 me-sm-2"></i>
                  <i class="fas fa-chess-knight text-warning me-1 me-sm-2"></i>
                  <i class="fas fa-chess-bishop text-light me-1 me-sm-2"></i>
                  <i class="fas fa-chess-queen text-warning me-1 me-sm-2"></i>
                  <i class="fas fa-chess-king text-light"></i>
                </div>
                <div class="chess-piece-row mt-2 mt-sm-3">
                  <i class="fas fa-chess-pawn text-light me-1 me-sm-2"></i>
                  <i class="fas fa-chess-pawn text-warning me-1 me-sm-2"></i>
                  <i class="fas fa-chess-pawn text-light me-1 me-sm-2"></i>
                  <i class="fas fa-chess-pawn text-warning me-1 me-sm-2"></i>
                  <i class="fas fa-chess-pawn text-light"></i>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- Features Section -->
    <section id="features" class="py-5">
      <div class="container">
        <div class="row text-center mb-5">
          <div class="col">
            <h2 class="display-5 fw-bold">Why Choose ChessBot?</h2>
            <p class="lead text-muted">Experience the perfect blend of challenge and learning</p>
          </div>
        </div>
        <div class="row g-4">
          <div class="col-md-4">
            <div class="card h-100 shadow-sm">
              <div class="card-body text-center">
                <i class="fas fa-camera fa-3x text-primary mb-3"></i>
                <h5 class="card-title">Photo Recognition</h5>
                <p class="card-text">
                  Simply take a photo of your chess board and our AI will instantly recognize all pieces and their positions.
                </p>
              </div>
            </div>
          </div>
          <div class="col-md-4">
            <div class="card h-100 shadow-sm">
              <div class="card-body text-center">
                <i class="fas fa-chess-board fa-3x text-success mb-3"></i>
                <h5 class="card-title">2D Visualization</h5>
                <p class="card-text">
                  Get a clean, professional 2D representation of your board position with accurate piece placement.
                </p>
              </div>
            </div>
          </div>
          <div class="col-md-4">
            <div class="card h-100 shadow-sm">
              <div class="card-body text-center">
                <i class="fas fa-brain fa-3x text-warning mb-3"></i>
                <h5 class="card-title">Position Analysis</h5>
                <p class="card-text">
                  Analyze any chess position with detailed insights, best moves, and strategic recommendations.
                </p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- Upload Section -->
    <section id="upload" class="bg-light py-4 py-md-5">
      <div class="container">
        <div class="row justify-content-center">
          <div class="col-lg-8">
            <h3 class="h4 h3-md fw-bold mb-3 text-dark text-center">Upload Your Chess Board</h3>
            <p class="mb-4 text-secondary fs-6 text-center">Take a photo of your real chess game and get instant position recognition and analysis.</p>
            
            <ImageUpload @board-recognized="handleBoardRecognition" />
            
            <div v-if="recognizedPosition" class="mt-5">
              <ChessBoard 
                :position="recognizedPosition.pieces" 
                :fen="recognizedPosition.fen"
                @analyze-position="analyzePosition"
              />
            </div>
          </div>
        </div>
      </div>
    </section>
  </div>
</template>

<script>
import ImageUpload from './ImageUpload.vue'
import ChessBoard from './ChessBoard.vue'

export default {
  name: 'Homepage',
  components: {
    ImageUpload,
    ChessBoard
  },
  data() {
    return {
      recognizedPosition: null
    }
  },
  methods: {
    scrollToUpload() {
      document.getElementById('upload').scrollIntoView({ 
        behavior: 'smooth' 
      });
    },
    
    scrollToFeatures() {
      document.getElementById('features').scrollIntoView({ 
        behavior: 'smooth' 
      });
    },
    
    handleBoardRecognition(result) {
      this.recognizedPosition = result;
      console.log('Board recognized:', result);
    },
    
    analyzePosition() {
      if (this.recognizedPosition && this.recognizedPosition.fen) {
        console.log('Analyzing position:', this.recognizedPosition.fen);
        // TODO: Implement position analysis
        alert('Position analysis coming soon!');
      }
    }
  }
}
</script>

<style scoped>
.hero-section {
  background-color: #1a1a1a;
}

.min-vh-mobile {
  min-height: 40vh;
}

@media (min-width: 768px) {
  .min-vh-mobile {
    min-height: 50vh;
  }
}

.chess-board-preview {
  filter: drop-shadow(0 5px 15px rgba(0,0,0,0.3));
}

@media (min-width: 768px) {
  .chess-board-preview {
    filter: drop-shadow(0 10px 20px rgba(0,0,0,0.3));
  }
}

.chess-pieces-display {
  padding: 1rem;
  background-color: rgba(255, 255, 255, 0.1);
  border-radius: 10px;
  backdrop-filter: blur(10px);
}

@media (min-width: 576px) {
  .chess-pieces-display {
    padding: 1.5rem;
    border-radius: 12px;
  }
}

@media (min-width: 768px) {
  .chess-pieces-display {
    padding: 2rem;
    border-radius: 15px;
  }
}

.chess-piece-row {
  font-size: 1.8rem;
}

@media (min-width: 576px) {
  .chess-piece-row {
    font-size: 2.2rem;
  }
}

@media (min-width: 768px) {
  .chess-piece-row {
    font-size: 2.5rem;
  }
}

.card {
  transition: transform 0.3s ease, box-shadow 0.3s ease;
  border: none;
}

/* Disable hover effects on touch devices */
@media (hover: hover) {
  .card:hover {
    transform: translateY(-5px);
    box-shadow: 0 8px 25px rgba(0,0,0,0.15);
  }
  
  .btn:hover {
    transform: translateY(-2px);
  }
}

.btn {
  transition: all 0.3s ease;
  border-width: 2px;
  min-height: 48px; /* Better touch target for mobile */
}

.btn-warning {
  background-color: #ffc107;
  border-color: #ffc107;
  color: #000;
}

.btn-warning:hover {
  background-color: #ffca2c;
  border-color: #ffca2c;
  color: #000;
}

/* Custom responsive utilities */
.h3-md {
  font-size: 1.75rem;
}

@media (min-width: 768px) {
  .h3-md {
    font-size: 1.875rem;
  }
}

.display-md-4 {
  font-size: 2.5rem;
}

@media (min-width: 768px) {
  .display-md-4 {
    font-size: 3.5rem;
  }
}

.fs-md-5 {
  font-size: 1rem;
}

@media (min-width: 768px) {
  .fs-md-5 {
    font-size: 1.25rem;
  }
}
</style>