import Echo from "laravel-echo";
import Pusher from "pusher-js";

window.Pusher = Pusher;

const esProduccion = window.location.pathname.startsWith('/sistema-tickets') || !['localhost', '127.0.0.1'].includes(window.location.hostname);

window.Echo = new Echo({
    broadcaster: "reverb",
    key: import.meta.env.VITE_REVERB_APP_KEY,
    wsHost: import.meta.env.VITE_REVERB_HOST,
    
    //----80 en produccion y 8080 en desarrollo, o el puerto definido en .env----//
    wsPort: esProduccion ? 80 : (import.meta.env.VITE_REVERB_PORT ?? 8080),
    wssPort: esProduccion ? 80 : (import.meta.env.VITE_REVERB_PORT ?? 8080),
    
    forceTLS: esProduccion ? false : ((import.meta.env.VITE_REVERB_SCHEME ?? "http") === "https"),
    
    enabledTransports: esProduccion ? ["ws"] : ["ws", "wss"],

    wsPath: esProduccion ? "/sistema-tickets" : undefined,
    authEndpoint: esProduccion ? "/sistema-tickets/api/broadcasting/auth" : "/broadcasting/auth",
});