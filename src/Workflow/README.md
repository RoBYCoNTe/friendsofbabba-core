# Leggimi

Questo workflow rappresenta un esempio da utilizzare come guida per la realizzazione
di qualisasi altro Business Process che richieda l'implementazione di una macchina
a stati.

## Come creare un workflow

Per creare un workflow completo:

-   Crea il tuo modello dati dell'entità da gestire direttamente in SQL.
-   Crea nella cartella src/Workflow/Entità l'oggetto Workflow.php
-   Crea i differenti stati in una sotto cartella di src/Workflow/Entità/States
-   Esponi, nel modello principale, la proprietà
    -   state
    -   notes
    -   is_private
    -   transaction
-   Genera la tabella di transazioni eseguendo questo comando: `bin/cake workflow create Entità`
-   Aggiungi la risorsa necessaria a mappare le transazioni in App.js: `<Resource name="workflow/transactions/entità" />`
