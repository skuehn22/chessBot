import './bootstrap';
import { createApp } from 'vue';

const app = createApp({
    data() {
        return {
            gameStarted: false
        }
    },
    methods: {
        startGame() {
            this.gameStarted = true;
            alert('Game started! This is Vue.js 3 working with Laravel and Bootstrap.');
        }
    }
});

app.mount('#app');
