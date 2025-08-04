import './bootstrap';
import { createApp } from 'vue';
import Homepage from './components/Homepage.vue';

const app = createApp({
    components: {
        Homepage
    },
    template: '<Homepage />'
});

app.mount('#app');
