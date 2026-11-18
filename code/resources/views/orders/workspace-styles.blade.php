<style>
    [x-cloak]{display:none!important}
    .service-workspace{--service-border:#e5e7eb;--service-surface:#fff;--service-map:#e9edf2;--service-muted:#64748b}
    .dark .service-workspace{--service-border:#475569;--service-surface:rgb(var(--body-bg));--service-map:#1f2937;--service-muted:#cbd5e1}
    .service-workspace .service-controls-box{margin-bottom:0;border-bottom-left-radius:0;border-bottom-right-radius:0;box-shadow:none}
    .service-workspace .service-content-box{border-top:0;border-top-left-radius:0;border-top-right-radius:0}
    .service-tabs{display:flex;gap:.5rem;overflow-x:auto;border-bottom:1px solid var(--service-border);padding:0 .25rem;flex-shrink:0}
    .service-tab{padding:.8rem 1.15rem;border-bottom:3px solid transparent;white-space:nowrap;font-weight:600;color:var(--service-muted)}
    .service-tab.active{color:rgb(var(--primary));border-color:rgb(var(--primary))}
    .service-workspace .o-toolbar{align-items:end}
    .service-workspace .o-toolbar>label{display:block;flex:1;min-width:min(180px,100%);font-weight:500}
    .service-workspace .o-toolbar>label>.form-control{display:block;margin-top:.4rem;width:100%}
    .service-workspace .o-toolbar>.form-control{flex:1;width:auto;min-width:min(200px,100%)}
    .service-workspace .o-dialog{border:1px solid var(--service-border);border-radius:.75rem;padding:1.25rem}
    .service-workspace .o-dialog .o-title{font-size:1.3125rem;font-weight:500}
    .service-form-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(min(220px,100%),1fr));gap:1rem;align-items:end;margin-bottom:1rem}
    .service-form-grid>label{display:block;min-width:0;font-weight:500}
    .service-form-grid>label>.form-control{display:block;margin-top:.4rem;width:100%}
    .service-form-grid>.ti-btn{justify-self:start;margin-bottom:0}
    .service-workspace .o-field>.form-control{width:100%}
    .service-workspace .qr-card{background:var(--service-surface);color:inherit;border-color:var(--service-border);border-radius:.65rem}
    @media print{.service-workspace .qr-card{background:#fff;color:#17243b}}
    .service-workspace .o-chip{border-radius:.5rem}
    .service-workspace .o-chip.active{background:rgb(var(--primary));border-color:rgb(var(--primary));color:#fff}
    .service-workspace .o-product:hover{border-color:rgb(var(--primary));background:rgb(var(--primary)/.06)}
    .service-workspace .o-badge{background:rgb(var(--primary)/.1);color:rgb(var(--primary))}
    .service-workspace .o-row{flex-wrap:wrap}
    .service-workspace .service-details{display:block;padding:1.25rem}
    .service-workspace summary{cursor:pointer;font-weight:600}
    .service-workspace .table th{background:rgb(var(--primary)/.06)}
    .service-workspace .table td,.service-workspace .table th{vertical-align:top}
    .service-workspace .box .box{box-shadow:none;border:1px solid var(--service-border)}
    .service-date-navigation{display:flex;align-items:center;justify-content:space-between;gap:.75rem;flex-wrap:wrap}
    .service-room-layout{display:flex;gap:1rem;align-items:stretch;min-height:520px}
    .service-room-map{position:relative;flex:1;min-width:0;overflow:auto;background:var(--service-map);border:1px solid var(--service-border);border-radius:.75rem;max-height:65vh}
    .service-room-inner{position:relative;margin:1rem}
    .service-orders-sidebar{order:-1;width:330px;flex:0 0 330px;max-height:65vh;overflow:auto}
    .service-order-card{border:1px solid var(--service-border);border-radius:.65rem;padding:.75rem;margin-bottom:.6rem;background:var(--service-surface)}
    .service-table{position:absolute;display:flex;align-items:center;justify-content:center;flex-direction:column;font-weight:700;border:2px solid;box-sizing:border-box;transition:box-shadow .2s}
    .service-table:hover,.service-table:focus-visible{box-shadow:0 0 0 4px rgb(var(--primary)/.25)}
    .service-table-free{background:#99c08c;border-color:#3e4552;color:#111827}
    .service-table-open{background:#fca5a5;border-color:#b91c1c;color:#111827}
    .service-table-paid{background:#bbf7d0;border-color:#15803d;color:#111827}
    .service-customers-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(min(300px,100%),1fr));gap:1rem}
    .service-workspace .o-board{align-items:start}
    .service-kds-column{border:1px solid var(--service-border);border-radius:.75rem;padding:.75rem;background:var(--service-map)}
    .service-workspace:fullscreen,.service-workspace:-webkit-full-screen{display:flex;flex-direction:column;width:100%;height:100%;min-height:0;overflow:auto;background:var(--service-surface);padding:1rem}
    .service-workspace:fullscreen .service-content-box,.service-workspace:-webkit-full-screen .service-content-box{display:flex;flex-direction:column;flex:1;min-height:0;margin-bottom:0}
    .service-workspace:fullscreen .service-content-body,.service-workspace:-webkit-full-screen .service-content-body{flex:1;min-height:0;overflow:auto}
    .service-workspace:fullscreen .service-room-layout,.service-workspace:-webkit-full-screen .service-room-layout{height:100%;min-height:400px}
    .service-workspace:fullscreen .service-room-map,.service-workspace:fullscreen .service-orders-sidebar,.service-workspace:-webkit-full-screen .service-room-map,.service-workspace:-webkit-full-screen .service-orders-sidebar{max-height:none}
    @media(max-width:900px){.service-room-layout{flex-direction:column}.service-orders-sidebar{order:-1;width:100%;flex-basis:auto;max-height:320px}.service-room-map{flex:auto;min-height:480px}.service-date-navigation{justify-content:center}}
</style>
