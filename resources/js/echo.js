import Echo from "laravel-echo";
import Pusher from "pusher-js";

window.Pusher = Pusher;

const esProduccion = window.location.pathname.startsWith('/sistema-tickets') || !['localhost', '127.0.0.1'].includes(window.location.hostname);

window.Echo = new Echo({
    broadcaster: "reverb",
    key: import.meta.env.VITE_REVERB_APP_KEY,
    wsHost: import.meta.env.VITE_REVERB_HOST,
    
    //----PRODUCCION PUERTO 80
    wsPort: import.meta.env.VITE_REVERB_PORT ?? (esProduccion ? 80 : 8080),
    wssPort: import.meta.env.VITE_REVERB_PORT ?? (esProduccion ? 80 : 8080),
    
    //-------SI NO UTILIZA HTTPS EN PRODUCCIÓN, ASEGURARSE DE QUE forceTLS ESTÉ EN false PARA EVITAR PROBLEMAS DE CONEXIÓN
    forceTLS: esProduccion ? false : ((import.meta.env.VITE_REVERB_SCHEME ?? "http") === "https"),
    enabledTransports: ["ws", "wss"],

    //----------Pusher añade de forma automática '/app/{key}'
    wsPath: esProduccion ? "/sistema-tickets" : undefined,
    
    authEndpoint: esProduccion ? "/sistema-tickets/api/broadcasting/auth" : "/broadcasting/auth",
});