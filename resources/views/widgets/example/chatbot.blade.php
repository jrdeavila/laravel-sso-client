@extends('sso-client::widgets.layout')

@section('widget-content')
<div
    x-data="{
        messages: [
            { from: 'bot', text: '¡Hola, {{ auth()->user()->name ?? 'funcionario' }}! Soy el chatbot de ejemplo.' }
        ],
        input: '',
        enviar() {
            if (!this.input.trim()) return;
            this.messages.push({ from: 'user', text: this.input });
            const msg = this.input;
            this.input = '';
            setTimeout(() => {
                this.messages.push({ from: 'bot', text: 'Recibí: ' + msg });
            }, 500);
        }
    }"
    style="display:flex; flex-direction:column; height:100vh; padding:10px; gap:8px; box-sizing:border-box;"
>
    <div style="flex:1; overflow-y:auto; display:flex; flex-direction:column; gap:6px;">
        <template x-for="(m, i) in messages" :key="i">
            <div
                :style="m.from === 'user'
                    ? 'align-self:flex-end; background:#d1e8ff; padding:6px 10px; border-radius:12px 12px 2px 12px; max-width:80%; font-size:13px;'
                    : 'align-self:flex-start; background:#f0f0f0; padding:6px 10px; border-radius:12px 12px 12px 2px; max-width:80%; font-size:13px;'"
                x-text="m.text"
            ></div>
        </template>
    </div>

    <div style="display:flex; gap:6px;">
        <input
            x-model="input"
            @keydown.enter="enviar()"
            type="text"
            placeholder="Escribe un mensaje..."
            style="flex:1; padding:7px 10px; border:1px solid #ccc; border-radius:8px; font-size:13px;"
        >
        <button @click="enviar()"
            style="padding:7px 14px; background:#3c8dbc; color:#fff; border:none; border-radius:8px; cursor:pointer; font-size:13px;">
            Enviar
        </button>
    </div>

    <div style="display:flex; gap:6px; justify-content:flex-end; padding-top:4px; border-top:1px solid #eee;">
        <button onclick="cCVSend('widget:submitted', { source: 'example-chatbot', test: true })"
            style="padding:4px 10px; font-size:11px; background:#00a65a; color:#fff; border:none; border-radius:6px; cursor:pointer;">
            Simular envío al lanzador
        </button>
        <button onclick="cCVSend('widget:close')"
            style="padding:4px 10px; font-size:11px; background:#dd4b39; color:#fff; border:none; border-radius:6px; cursor:pointer;">
            Cerrar widget
        </button>
    </div>
</div>
@endsection
