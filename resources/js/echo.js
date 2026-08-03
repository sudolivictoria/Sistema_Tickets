import Echo from "laravel-echo";
import Pusher from "pusher-js";

window.Pusher = Pusher;

const esProduccion = window.location.pathname.startsWith('/sistema-tickets') || !['localhost', '127.0.0.1'].includes(window.location.hostname);

//----detectar si se carga con https
const esHttps = window.location.protocol === 'https:';

window.Echo = new Echo({
    broadcaster: "reverb",
    key: import.meta.env.VITE_REVERB_APP_KEY,
    wsHost: import.meta.env.VITE_REVERB_HOST,
    
    //----puerto 80 para HTTP normal, puerto 443 para HTTPS (WSS)
    wsPort: esProduccion ? 80 : (import.meta.env.VITE_REVERB_PORT ?? 8080),
    wssPort: esProduccion ? 443 : (import.meta.env.VITE_REVERB_PORT ?? 8080),
    
    //----forzar tls si esta en https
    forceTLS: esProduccion ? esHttps : ((import.meta.env.VITE_REVERB_SCHEME ?? "http") === "https"),
    
    //----transporte segun el protocolo
    enabledTransports: esProduccion 
        ? (esHttps ? ["wss"] : ["ws"]) 
        : ["ws", "wss"],

    wsPath: esProduccion ? "/sistema-tickets" : undefined,
    authEndpoint: esProduccion ? "/sistema-tickets/api/broadcasting/auth" : "/broadcasting/auth",
});