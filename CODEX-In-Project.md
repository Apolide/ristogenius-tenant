Per usare l'estensione Codex di OpenAI in VS Code su uno specifico progetto, devi aprire direttamente la cartella del singolo progetto all'interno dell'editor e configurare il contesto. [1] (https://www.reddit.com/r/codex/comments/1r6u3sd/how_do_i_get_vs_code_codex_to_work_in_a_single/), [2] (https://yepsoso.com/codex/vscode/)


PUOI FARLO DA RIGA DI COMANDO CON:
toni@toni-ThinkPad-T14-Gen-1:~/Projects/pv-designer$ 
    ( avvia progetto con codex locale di progetto )
    CODEX_HOME="$(pwd)/.codex" code .

    ( crea ./.codex direttamente nel progetto se non esiste)
    CODEX_HOME="$(pwd)/.codex" code .



Come configurare Codex per un singolo progetto

1. Apri la cartella corretta: Assicurati di aprire solo la cartella del progetto specifico (File > Open Folder...) ed evita di usare un'area di lavoro (workspace) multi-progetto o la root di sistema, altrimenti Codex potrebbe accedere a file non correlati. [1] (https://github.com/openai/codex/issues/11009), [2] (https://www.reddit.com/r/codex/comments/1r6u3sd/how_do_i_get_vs_code_codex_to_work_in_a_single/)
2. Avvia il pannello Codex: Clicca sull'icona di Codex nella barra laterale di VS Code oppure apri il Command Palette (Ctrl+Shift+P / Cmd+Shift+P) e digita Codex: Open Codex Sidebar. [1] (https://learn.chatgpt.com/docs/codex/ide), [2] (https://www.youtube.com/watch?v=QbPYL2mIdBk)
3. Accedi all'account: Effettua il login con il tuo account ChatGPT (Plus, Pro, Business, Edu o Enterprise). [1] (https://marketplace.visualstudio.com/items?itemName=openai.chatgpt), [2] (https://www.youtube.com/watch?v=QbPYL2mIdBk)
4. Limita il contesto: Aggiungi esplicitamente alla chat solo i file o le sottocartelle che Codex deve modificare, oppure sfrutta la modalità piano (plan mode) per visionare e approvare le modifiche prima che vengano scritte sul disco. [1] (https://www.youtube.com/watch?v=tmlPJMbwodc&t=186), [2] (https://www.reddit.com/r/codex/comments/1r6u3sd/how_do_i_get_vs_code_codex_to_work_in_a_single/)
5. Usa i file di ignore: Se lavori in workspace complessi, puoi inserire un file .codexignore o .gitignore per impedire all'agente di scansionare directory esterne al progetto. [1] (https://www.reddit.com/r/codex/comments/1r6u3sd/how_do_i_get_vs_code_codex_to_work_in_a_single/)
Puoi scaricare o aggiornare l'estensione direttamente dal Visual Studio Marketplace - Codex by OpenAI. [1] (https://marketplace.visualstudio.com/items?itemName=openai.chatgpt)
Guarda questa guida video su come impostare e usare l'estensione Codex all'interno dei tuoi progetti in VS Code:
• Stai riscontrando problemi con più cartelle aperte nello stesso workspace?
• Vuoi sapere come configurare il file .config.toml per personalizzare il comportamento di Codex?
