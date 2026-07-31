import Echo from "laravel-echo";
import Pusher from "pusher-js";

window.Pusher = Pusher;

const esProduccion = window.location.pathname.startsWith('/sistema-tickets') || !['localhost', '127.0.0.1'].includes(window.location.hostname);

// 1. Detectamos automáticamente si la página actual cargó con HTTPS
const esHttps = window.location.protocol === 'https:';

window.Echo = new Echo({
    broadcaster: "reverb",
    key: import.meta.env.VITE_REVERB_APP_KEY,
    wsHost: import.meta.env.VITE_REVERB_HOST,
    
    // 2. Puerto 80 para HTTP normal, Puerto 443 para HTTPS (WSS)
    wsPort: esProduccion ? 80 : (import.meta.env.VITE_REVERB_PORT ?? 8080),
    wssPort: esProduccion ? 443 : (import.meta.env.VITE_REVERB_PORT ?? 8080),
    
    // 3. Forzamos TLS (seguridad) solo si la página está en HTTPS
    forceTLS: esProduccion ? esHttps : ((import.meta.env.VITE_REVERB_SCHEME ?? "http") === "https"),
    
    // 4. Habilitamos el transporte correcto según el protocolo
    enabledTransports: esProduccion 
        ? (esHttps ? ["wss"] : ["ws"]) 
        : ["ws", "wss"],

    wsPath: esProduccion ? "/sistema-tickets" : undefined,
    authEndpoint: esProduccion ? "/sistema-tickets/api/broadcasting/auth" : "/broadcasting/auth",
});