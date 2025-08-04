<template>
  <div class="chess-board-container">
    <div class="board-header mb-3">
      <h5 class="mb-2">Recognized Chess Position</h5>
      <p class="text-muted small mb-0" v-if="fen">
        FEN: {{ fen }}
      </p>
    </div>
    
    <div class="chess-board-wrapper">
      <div class="board-coordinates">
        <div class="rank-labels">
          <div 
            v-for="rank in ranks" 
            :key="rank" 
            class="rank-label"
          >
            {{ rank }}
          </div>
        </div>
        
        <div class="board-content">
          <svg 
            class="chess-board" 
            :width="boardSize" 
            :height="boardSize"
            viewBox="0 0 400 400"
          >
            <!-- Board squares -->
            <g class="squares">
              <rect
                v-for="(square, index) in squares"
                :key="index"
                :x="square.x"
                :y="square.y"
                :width="squareSize"
                :height="squareSize"
                :class="['square', square.color]"
              />
            </g>
            
            <!-- Chess pieces -->
            <g class="pieces">
              <text
                v-for="piece in pieces"
                :key="piece.square"
                :x="piece.x + squareSize / 2"
                :y="piece.y + squareSize / 2"
                class="chess-piece"
                :class="[piece.color]"
                text-anchor="middle"
                dominant-baseline="central"
              >
                {{ piece.symbol }}
              </text>
            </g>
            
            <!-- Grid lines -->
            <g class="grid-lines">
              <line
                v-for="i in 9"
                :key="'v' + i"
                :x1="(i - 1) * squareSize"
                :y1="0"
                :x2="(i - 1) * squareSize"
                :y2="400"
                class="grid-line"
              />
              <line
                v-for="i in 9"
                :key="'h' + i"
                :x1="0"
                :y1="(i - 1) * squareSize"
                :x2="400"
                :y2="(i - 1) * squareSize"
                class="grid-line"
              />
            </g>
          </svg>
          
          <div class="file-labels">
            <div 
              v-for="file in files" 
              :key="file" 
              class="file-label"
            >
              {{ file }}
            </div>
          </div>
        </div>
      </div>
    </div>
    
    <div class="board-actions mt-3">
      <div class="d-flex flex-wrap gap-2 justify-content-center">
        <button 
          class="btn btn-outline-primary btn-sm"
          @click="copyFen"
          v-if="fen"
        >
          <i class="fas fa-copy me-1"></i>Copy FEN
        </button>
        <button 
          class="btn btn-outline-secondary btn-sm"
          @click="flipBoard"
        >
          <i class="fas fa-sync-alt me-1"></i>Flip Board
        </button>
        <button 
          class="btn btn-outline-success btn-sm"
          @click="$emit('analyze-position')"
          v-if="fen"
        >
          <i class="fas fa-brain me-1"></i>Analyze Position
        </button>
      </div>
    </div>
    
    <div v-if="analysisResult" class="analysis-section mt-4">
      <div class="card">
        <div class="card-header">
          <h6 class="mb-0">Position Analysis</h6>
        </div>
        <div class="card-body">
          <p class="mb-2"><strong>Best Move:</strong> {{ analysisResult.bestMove }}</p>
          <p class="mb-2"><strong>Evaluation:</strong> {{ analysisResult.evaluation }}</p>
          <p class="mb-0 small text-muted">{{ analysisResult.description }}</p>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'ChessBoard',
  props: {
    position: {
      type: Object,
      default: () => ({})
    },
    fen: {
      type: String,
      default: ''
    }
  },
  emits: ['analyze-position'],
  data() {
    return {
      boardSize: 400,
      squareSize: 50,
      flipped: false,
      analysisResult: null,
      pieceSymbols: {
        'K': '♔', 'Q': '♕', 'R': '♖', 'B': '♗', 'N': '♘', 'P': '♙',
        'k': '♚', 'q': '♛', 'r': '♜', 'b': '♝', 'n': '♞', 'p': '♟'
      }
    }
  },
  computed: {
    files() {
      const files = ['a', 'b', 'c', 'd', 'e', 'f', 'g', 'h'];
      return this.flipped ? files.reverse() : files;
    },
    
    ranks() {
      const ranks = [8, 7, 6, 5, 4, 3, 2, 1];
      return this.flipped ? ranks.reverse() : ranks;
    },
    
    squares() {
      const squares = [];
      for (let rank = 0; rank < 8; rank++) {
        for (let file = 0; file < 8; file++) {
          const isLight = (rank + file) % 2 === 0;
          squares.push({
            x: file * this.squareSize,
            y: rank * this.squareSize,
            color: isLight ? 'light' : 'dark',
            square: this.getSquareName(file, rank)
          });
        }
      }
      return squares;
    },
    
    pieces() {
      if (!this.position || Object.keys(this.position).length === 0) {
        return this.getStartingPosition();
      }
      
      const pieces = [];
      Object.entries(this.position).forEach(([square, piece]) => {
        const coords = this.squareToCoordinates(square);
        if (coords && this.pieceSymbols[piece]) {
          pieces.push({
            square,
            x: coords.x,
            y: coords.y,
            symbol: this.pieceSymbols[piece],
            color: piece === piece.toUpperCase() ? 'white' : 'black',
            piece
          });
        }
      });
      
      return pieces;
    }
  },
  methods: {
    getSquareName(file, rank) {
      const files = ['a', 'b', 'c', 'd', 'e', 'f', 'g', 'h'];
      const adjustedFile = this.flipped ? 7 - file : file;
      const adjustedRank = this.flipped ? rank + 1 : 8 - rank;
      return files[adjustedFile] + adjustedRank;
    },
    
    squareToCoordinates(square) {
      const file = square.charCodeAt(0) - 97; // 'a' = 0, 'b' = 1, etc.
      const rank = parseInt(square[1]) - 1; // '1' = 0, '2' = 1, etc.
      
      if (file < 0 || file > 7 || rank < 0 || rank > 7) {
        return null;
      }
      
      const adjustedFile = this.flipped ? 7 - file : file;
      const adjustedRank = this.flipped ? rank : 7 - rank;
      
      return {
        x: adjustedFile * this.squareSize,
        y: adjustedRank * this.squareSize
      };
    },
    
    getStartingPosition() {
      const startingFen = "rnbqkbnr/pppppppp/8/8/8/8/PPPPPPPP/RNBQKBNR";
      const position = this.fenToPosition(startingFen);
      const pieces = [];
      
      Object.entries(position).forEach(([square, piece]) => {
        const coords = this.squareToCoordinates(square);
        if (coords && this.pieceSymbols[piece]) {
          pieces.push({
            square,
            x: coords.x,
            y: coords.y,
            symbol: this.pieceSymbols[piece],
            color: piece === piece.toUpperCase() ? 'white' : 'black',
            piece
          });
        }
      });
      
      return pieces;
    },
    
    fenToPosition(fen) {
      const position = {};
      const ranks = fen.split('/');
      
      for (let rankIndex = 0; rankIndex < ranks.length; rankIndex++) {
        const rank = ranks[rankIndex];
        let fileIndex = 0;
        
        for (let i = 0; i < rank.length; i++) {
          const char = rank[i];
          
          if (isNaN(char)) {
            // It's a piece
            const square = String.fromCharCode(97 + fileIndex) + (8 - rankIndex);
            position[square] = char;
            fileIndex++;
          } else {
            // It's a number of empty squares
            fileIndex += parseInt(char);
          }
        }
      }
      
      return position;
    },
    
    flipBoard() {
      this.flipped = !this.flipped;
    },
    
    copyFen() {
      if (this.fen) {
        navigator.clipboard.writeText(this.fen).then(() => {
          // Could add a toast notification here
          console.log('FEN copied to clipboard');
        });
      }
    }
  },
  
  mounted() {
    // If no position provided, show starting position
    if (!this.position || Object.keys(this.position).length === 0) {
      // This will trigger the computed property to show starting position
    }
  }
}
</script>

<style scoped>
.chess-board-container {
  max-width: 500px;
  margin: 0 auto;
}

.chess-board-wrapper {
  display: inline-block;
  background: #8B4513;
  padding: 20px;
  border-radius: 10px;
  box-shadow: 0 4px 12px rgba(0,0,0,0.2);
}

.board-coordinates {
  position: relative;
}

.rank-labels {
  position: absolute;
  left: -15px;
  top: 0;
  height: 400px;
  display: flex;
  flex-direction: column;
  justify-content: space-around;
}

.rank-label {
  height: 50px;
  display: flex;
  align-items: center;
  font-weight: bold;
  color: #654321;
  font-size: 14px;
}

.file-labels {
  display: flex;
  justify-content: space-around;
  margin-top: 5px;
}

.file-label {
  width: 50px;
  text-align: center;
  font-weight: bold;
  color: #654321;
  font-size: 14px;
}

.chess-board {
  display: block;
  border: 2px solid #654321;
  background: #F0D9B5;
}

.square.light {
  fill: #F0D9B5;
}

.square.dark {
  fill: #B58863;
}

.grid-line {
  stroke: #654321;
  stroke-width: 0.5;
  opacity: 0.3;
}

.chess-piece {
  font-size: 32px;
  font-weight: bold;
  cursor: default;
  user-select: none;
}

.chess-piece.white {
  fill: #FFFFFF;
}

.chess-piece.black {
  fill: #000000;
}

.board-header {
  text-align: center;
}

.board-actions {
  text-align: center;
}

.analysis-section {
  max-width: 100%;
}

@media (max-width: 576px) {
  .chess-board-wrapper {
    padding: 15px;
  }
  
  .rank-labels {
    left: -12px;
  }
  
  .rank-label,
  .file-label {
    font-size: 12px;
  }
  
  .chess-piece {
    font-size: 24px;
  }
}

@media (max-width: 480px) {
  .chess-board {
    width: 320px;
    height: 320px;
  }
  
  .chess-board-container {
    max-width: 350px;
  }
}
</style>