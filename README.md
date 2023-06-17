# futurosolareprevede
Sistema di previsione per l'auto

# Principio di funzionamento

1° step
Si carica una pagina e si visualizza come posizione di inizio quella fornita dal GPS dell'auto, se disponibile
altrimenti chiedere posizione direttamente al dispositivo tramite javascript.

2° step
Data la serie di punti presa li individuiamo a gruppi di 3 che si ridurrà in 2. 
Per fare ciò avremo a disposizione un algoritmo che confronta la posizione attuale con tutte le altre: così individuiamo il punto più vicino alla nostra posizione attuale;
Adesso sarà necessario caricare e visualizzare la root da seguire per raggiungere il punto successivo (grazie al Time To Next)
Successivamente svilupperemo un'API e implementeremo una funzione si limite max di velocità (Servendoci di OPen Street Map)
