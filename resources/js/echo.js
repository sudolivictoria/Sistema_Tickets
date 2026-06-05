import Echo from "laravel-echo";
import Pusher from "pusher-js";

window.Pusher = Pusher;

const esProduccion = window.location.pathname.startsWith('/sistema-tickets') || !['localhost', '127.0.0.1'].includes(window.location.hostname);

window.Echo = new Echo({
    broadcaster: "reverb",
    key: import.meta.env.VITE_REVERB_APP_KEY,
    wsHost: import.meta.env.VITE_REVERB_HOST,
    
    wsPort: import.meta.env.VITE_REVERB_PORT ?? (esProduccion ? 80 : 8080),
    wssPort: import.meta.env.VITE_REVERB_PORT ?? (esProduccion ? 80 : 8080),
    
    forceTLS: (import.meta.env.VITE_REVERB_SCHEME ?? "http") === "https",
    enabledTransports: ["ws", "wss"],

    wsPath: esProduccion ? "/sistema-tickets/app" : undefined,
    authEndpoint: esProduccion ? "/sistema-tickets/api/broadcasting/auth" : "/broadcasting/auth",
});