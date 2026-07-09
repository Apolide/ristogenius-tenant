<div class="content">
    <div class="main-content">
        <div class="md:flex block items-center justify-between mb-6 page-header-breadcrumb">
            <div class="my-auto">
                <h5 class="page-title text-[1.3125rem] font-medium text-defaulttextcolor mb-0">Impostazioni</h5>
                <nav>
                    <ol class="flex items-center whitespace-nowrap min-w-0">
                        <li class="text-[12px]">
                            <a class="flex items-center text-primary hover:text-primary" href="/">
                                Home
                                <i class="ti ti-chevrons-right flex-shrink-0 mx-3 overflow-visible text-textmuted rtl:rotate-180"></i>
                            </a>
                        </li>
                        <li class="text-[12px]">
                            <a class="flex items-center text-primary hover:text-primary" href="{{ route('settings.rooms') }}">
                                Sala
                                <i class="ti ti-chevrons-right flex-shrink-0 mx-3 overflow-visible text-textmuted rtl:rotate-180"></i>
                            </a>
                        </li>
                        <li class="text-[12px]">
                            <a class="flex items-center text-textmuted" href="javascript:void(0);">Mappatura sala</a>
                        </li>
                    </ol>
                </nav>
            </div>
        </div>

        <div class="box-timeslot box-realtime">
            <div wire:ignore x-data="roomTablePlanner(@js($layout), @js($tables), @js($mode))">
                <style>
                    .room-layout {
                        position: relative;
                        width: 100%;
                        max-width: 100%;
                        overflow: auto;
                        border-radius: 0.75rem;
                        border: 1px solid #053ca8;
                    }

                    .room-inner {
                        position: relative;
                        margin: 1rem;
                    }

                    .table-item {
                        position: absolute;
                        display: flex;
                        align-items: center;
                        justify-content: center;
                        border: 2px solid #3e4552;
                        background: #99c08c;
                        color: inherit;
                        text-decoration: none;
                        user-select: none;
                        touch-action: none;
                    }

                    .table-square {
                        border-radius: 0.5rem;
                    }

                    .table-rectangle {
                        border-radius: 0.5rem;
                    }

                    .table-circle {
                        border-radius: 9999px;
                    }

                    .table-free {
                        box-shadow: 0 1px 2px rgba(0, 0, 0, 0.08);
                    }

                    .table-busy {
                        background: #fee2e2;
                        border-color: #b91c1c;
                    }

                    .table-reserved {
                        background: #fef9c3;
                        border-color: #a16207;
                    }

                    .table-selected {
                        outline: 3px dashed #3b82f6;
                        outline-offset: 2px;
                    }

                    .lasso {
                        position: absolute;
                        border: 1px dashed #60a5fa;
                        background: rgba(96, 165, 250, 0.15);
                        pointer-events: none;
                    }

                    .rot-handle {
                        position: absolute;
                        top: -16px;
                        left: 50%;
                        width: 14px;
                        height: 14px;
                        transform: translateX(-50%);
                        border-radius: 9999px;
                        background: #fff;
                        border: 2px solid #3b82f6;
                        box-shadow: 0 1px 2px rgba(0, 0, 0, .15);
                        cursor: grab;
                        touch-action: none;
                    }

                    .top-bar {
                        display: flex;
                        justify-content: space-between;
                        margin-bottom: 20px;
                        align-items: center;
                        gap: 1rem;
                    }

                    .room-selection {
                        display: flex;
                        align-items: center;
                        gap: 0.5rem;
                    }

                    .select-room {
                        padding: 5px;
                        border: 1px solid #ccc;
                        border-radius: 4px;
                    }

                    .room-info {
                        margin-bottom: 20px;
                    }

                    .room-name {
                        margin: 0 0 5px;
                        font-size: 24px;
                        font-weight: bold;
                    }

                    .room-details {
                        margin: 0;
                        font-size: 14px;
                        color: #666;
                    }
                </style>

                <div class="top-bar">
                    <div class="room-selection">
                        <label for="room-select" class="font-bold">Seleziona sala:</label>
                        <select id="room-select" class="select-room" x-on:change="changeRoom($event.target.value)">
                            @foreach ($rooms as $selectableRoom)
                                <option value="{{ $selectableRoom->id }}" @selected($currentRoom && $selectableRoom->id === $currentRoom->id)>
                                    {{ $selectableRoom->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mode-buttons">
                        <button class="ti-btn ti-btn-success" :class="{'ti-btn-success-full': mode === 'view'}" x-on:click="setMode('view')">Visualizzazione</button>
                        <button class="ti-btn ti-btn-primary" :class="{'ti-btn-primary-full': mode === 'edit'}" x-on:click="setMode('edit')">Modifica</button>
                    </div>
                </div>

                @if ($currentRoom)
                    <div class="room-info">
                        <h2 class="room-name">{{ $currentRoom->name }}</h2>
                        <p class="room-details">Capacita: {{ $currentRoom->capacity }} | Fumatori: {{ $currentRoom->smoking_allowed ? 'Si' : 'No' }}</p>
                    </div>

                    <div class="toolbar flex flex-wrap items-center gap-5" x-show="mode === 'edit'">
                        <div class="mb-4">
                            <h5>Strumento</h5>
                            <span class="block text-xs text-gray-500 mb-1">Da mobile</span>
                            <p>
                                <button @click="tool='select'" :class="['ti-btn', tool==='select' ? 'ti-btn-primary-full' : 'ti-btn-primary']">Seleziona</button>
                                <button @click="tool='pan'" :class="['ti-btn', tool==='pan' ? 'ti-btn-primary-full' : 'ti-btn-primary']">Pan</button>
                            </p>
                        </div>

                        <div class="mb-4">
                            <h5>Aggiungi Tavoli</h5>
                            <div class="flex flex-wrap gap-3">
                                <div>
                                    <span class="block text-xs text-gray-500 mb-1">Forma</span>
                                    <select x-model="newType" class="form-control">
                                        <option value="circolare">Circolare</option>
                                        <option value="quadrato">Quadrato</option>
                                        <option value="rettangolare">Rettangolare</option>
                                    </select>
                                </div>
                                <div>
                                    <span class="block text-xs text-gray-500 mb-1">Numero</span>
                                    <input type="number" x-model.number="newCount" min="1" class="w-16 form-control">
                                </div>
                                <div>
                                    <span class="block text-xs text-gray-500 mb-1">Colonne</span>
                                    <input type="number" x-model.number="newCols" min="1" class="w-16 form-control">
                                </div>
                                <div>
                                    <span class="block text-xs text-gray-500 mb-1">Prefisso</span>
                                    <input type="text" x-model="newPrefix" class="w-24 form-control">
                                </div>
                                <div>
                                    <span class="block text-xs text-gray-500 mb-1">Azione</span>
                                    <button type="button" class="ti-btn ti-btn-primary" @click="$wire.dispatch('planner.add-many', { type:newType, count:newCount, cols:newCols, prefix:newPrefix })">
                                        Aggiungi in griglia
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="room-layout mt-4" x-ref="scroller">
                        <div class="room-inner" x-ref="inner" :style="innerStyle + ';touch-action:' + (tool === 'select' ? 'none' : 'pan-x pan-y')" @pointerdown.prevent="onBackgroundDown">
                            <template x-for="t in tables" :key="t.id">
                                <a class="table-item" :class="tableClasses(t)" :style="tableStyle(t)" @pointerdown.stop="onTablePointerDown($event, t)" @click.stop="onTableClick($event, t)">
                                    <div style="display:flex;flex-direction:column;align-items:center;">
                                        <strong class="text-black" x-text="t.label || ''"></strong>
                                    </div>

                                    <div class="rot-handle" x-show="mode === 'edit'" @pointerdown.stop="onRotateDown($event, t)"></div>
                                </a>
                            </template>

                            <div class="lasso" x-show="lasso.active" :style="`left:${lasso.left}px;top:${lasso.top}px;width:${lasso.w}px;height:${lasso.h}px`"></div>
                        </div>
                    </div>
                @else
                    <p>Nessuna sala disponibile.</p>
                @endif
            </div>
        </div>

        <script>
            document.addEventListener('alpine:init', () => {
                const toPlain = (value) => {
                    try {
                        return structuredClone(value);
                    } catch {
                        return JSON.parse(JSON.stringify(value));
                    }
                };

                Alpine.data('roomTablePlanner', (room, tables, mode) => {
                    const normalizeRoom = (source) => ({
                        width: +(source?.width || 1200),
                        height: +(source?.height || 700),
                        grid: Math.max(5, +(source?.grid || 20)),
                    });

                    const normalizeTables = (source) => (Array.isArray(source) ? source : [])
                        .filter((table) => table && table.id)
                        .map((table) => ({
                            id: table.id,
                            label: table.label ?? '',
                            type: ['circolare', 'quadrato', 'rettangolare'].includes(table.type) ? table.type : 'quadrato',
                            status: table.status || 'free',
                            x: +table.x || 0,
                            y: +table.y || 0,
                            w: +table.w || (table.type === 'rettangolare' ? 98 : 78),
                            h: +table.h || 78,
                            rotation: +table.rotation || 0,
                        }));

                    return {
                        room: normalizeRoom(room),
                        tables: normalizeTables(tables),
                        mode: mode || 'view',
                        tool: 'select',
                        selected: [],
                        dragging: { active: false, startX: 0, startY: 0, origin: [] },
                        lasso: { active: false, startX: 0, startY: 0, left: 0, top: 0, w: 0, h: 0 },
                        rotating: { active: false, ids: [], startAngle: 0, startRotations: new Map() },
                        panning: { active: false, startX: 0, startY: 0, scrollLeft: 0, scrollTop: 0 },
                        newType: 'quadrato',
                        newCount: 6,
                        newCols: 3,
                        newPrefix: '',

                        init() {
                            window.addEventListener('planner.refresh', (event) => {
                                this.tables = normalizeTables(event.detail.tables || []);
                                this.selected = [];
                            });

                            window.addEventListener('keydown', (event) => {
                                if (this.mode !== 'edit') return;

                                if (event.key === 'Escape') this.selected = [];

                                if ((event.metaKey || event.ctrlKey) && event.key.toLowerCase() === 'a') {
                                    event.preventDefault();
                                    this.selected = this.tables.map((table) => table.id);
                                }

                                if (['ArrowUp', 'ArrowDown', 'ArrowLeft', 'ArrowRight'].includes(event.key) && this.selected.length) {
                                    event.preventDefault();
                                    const step = event.shiftKey ? this.room.grid : 1;
                                    const dx = (event.key === 'ArrowRight' ? step : 0) - (event.key === 'ArrowLeft' ? step : 0);
                                    const dy = (event.key === 'ArrowDown' ? step : 0) - (event.key === 'ArrowUp' ? step : 0);
                                    this.nudgeSelected(dx, dy, true);
                                }
                            });
                        },

                        snap(value) {
                            return Math.round(value / this.room.grid) * this.room.grid;
                        },

                        tableClasses(table) {
                            return [
                                table.type === 'circolare' ? 'table-circle' : (table.type === 'rettangolare' ? 'table-rectangle' : 'table-square'),
                                table.status === 'busy' ? 'table-busy' : (table.status === 'reserved' ? 'table-reserved' : 'table-free'),
                                this.selected.includes(table.id) ? 'table-selected' : '',
                            ];
                        },

                        tableStyle(table) {
                            return `transform:translate(${table.x}px, ${table.y}px) rotate(${table.rotation}deg);width:${table.w}px;height:${table.h}px;`;
                        },

                        changeRoom(roomId) {
                            this.$wire.dispatch('room.changed', { roomId });
                        },

                        setMode(newMode) {
                            this.mode = newMode === 'edit' ? 'edit' : 'view';
                            this.$wire.call('setMode', this.mode);
                        },

                        onTablePointerDown(event, table) {
                            if (this.mode !== 'edit' || this.tool !== 'select') return;

                            if (!(event.shiftKey || event.metaKey || this.selected.includes(table.id))) {
                                this.selected = [table.id];
                            }

                            this.dragging = {
                                active: true,
                                startX: event.clientX,
                                startY: event.clientY,
                                origin: this.tables.map((item) => ({ id: item.id, x: item.x, y: item.y })),
                            };

                            const move = (moveEvent) => {
                                if (!this.dragging.active) return;

                                const dx = moveEvent.clientX - this.dragging.startX;
                                const dy = moveEvent.clientY - this.dragging.startY;

                                this.tables = this.tables.map((item) => {
                                    if (!this.selected.includes(item.id)) return item;

                                    const origin = this.dragging.origin.find((candidate) => candidate.id === item.id);
                                    return {
                                        ...item,
                                        x: Math.max(0, this.snap(origin.x + dx)),
                                        y: Math.max(0, this.snap(origin.y + dy)),
                                    };
                                });
                            };

                            const up = () => {
                                this.dragging.active = false;
                                window.removeEventListener('pointermove', move);
                                window.removeEventListener('pointerup', up);
                                this.saveNow();
                            };

                            window.addEventListener('pointermove', move);
                            window.addEventListener('pointerup', up);
                        },

                        onTableClick() {},

                        onBackgroundDown(event) {
                            if (this.mode !== 'edit') return;

                            if (this.tool === 'pan') {
                                const scroller = this.$refs.scroller;
                                this.panning = {
                                    active: true,
                                    startX: event.clientX,
                                    startY: event.clientY,
                                    scrollLeft: scroller.scrollLeft,
                                    scrollTop: scroller.scrollTop,
                                };

                                const move = (moveEvent) => {
                                    if (!this.panning.active) return;
                                    scroller.scrollLeft = this.panning.scrollLeft - (moveEvent.clientX - this.panning.startX);
                                    scroller.scrollTop = this.panning.scrollTop - (moveEvent.clientY - this.panning.startY);
                                };

                                const up = () => {
                                    this.panning.active = false;
                                    window.removeEventListener('pointermove', move);
                                    window.removeEventListener('pointerup', up);
                                };

                                window.addEventListener('pointermove', move);
                                window.addEventListener('pointerup', up);
                                return;
                            }

                            const point = (pointerEvent) => {
                                const rect = this.$refs.inner.getBoundingClientRect();
                                return { x: pointerEvent.clientX - rect.left, y: pointerEvent.clientY - rect.top };
                            };
                            const start = point(event);

                            this.lasso = {
                                active: true,
                                startX: start.x,
                                startY: start.y,
                                left: start.x,
                                top: start.y,
                                w: 0,
                                h: 0,
                            };

                            const move = (moveEvent) => {
                                const current = point(moveEvent);
                                this.lasso.left = Math.min(start.x, current.x);
                                this.lasso.top = Math.min(start.y, current.y);
                                this.lasso.w = Math.abs(current.x - start.x);
                                this.lasso.h = Math.abs(current.y - start.y);
                            };

                            const up = () => {
                                const left = this.lasso.left;
                                const top = this.lasso.top;
                                const right = left + this.lasso.w;
                                const bottom = top + this.lasso.h;

                                this.selected = this.lasso.w < 3 && this.lasso.h < 3
                                    ? []
                                    : this.tables
                                        .filter((table) => (table.x + table.w) > left && table.x < right && (table.y + table.h) > top && table.y < bottom)
                                        .map((table) => table.id);

                                this.lasso.active = false;
                                window.removeEventListener('pointermove', move);
                                window.removeEventListener('pointerup', up);
                            };

                            window.addEventListener('pointermove', move);
                            window.addEventListener('pointerup', up);
                        },

                        angleAtEvent(element, event) {
                            const rect = element.getBoundingClientRect();
                            return Math.atan2(event.clientY - (rect.top + rect.height / 2), event.clientX - (rect.left + rect.width / 2)) * 180 / Math.PI;
                        },

                        normalizeAngle(angle) {
                            const normalized = angle % 360;
                            return Math.round(normalized < 0 ? normalized + 360 : normalized);
                        },

                        onRotateDown(event, table) {
                            if (this.mode !== 'edit') return;

                            if (!this.selected.includes(table.id)) {
                                this.selected = [table.id];
                            }

                            const element = event.currentTarget.closest('.table-item');
                            this.rotating = {
                                active: true,
                                ids: [...this.selected],
                                startAngle: this.angleAtEvent(element, event),
                                startRotations: new Map(this.selected.map((id) => [id, this.tables.find((item) => item.id === id)?.rotation ?? 0])),
                            };

                            const move = (moveEvent) => {
                                const delta = this.angleAtEvent(element, moveEvent) - this.rotating.startAngle;
                                const step = moveEvent.shiftKey ? 1 : 15;

                                this.tables = this.tables.map((item) => {
                                    if (!this.rotating.ids.includes(item.id)) return item;
                                    const base = this.rotating.startRotations.get(item.id) ?? 0;
                                    return { ...item, rotation: Math.round(this.normalizeAngle(base + delta) / step) * step };
                                });
                            };

                            const up = () => {
                                this.rotating.active = false;
                                window.removeEventListener('pointermove', move);
                                window.removeEventListener('pointerup', up);
                                this.saveNow();
                            };

                            window.addEventListener('pointermove', move);
                            window.addEventListener('pointerup', up);
                        },

                        nudgeSelected(dx, dy, snap = false) {
                            this.tables = this.tables.map((table) => {
                                if (!this.selected.includes(table.id)) return table;
                                const x = table.x + dx;
                                const y = table.y + dy;
                                return {
                                    ...table,
                                    x: Math.max(0, snap ? this.snap(x) : x),
                                    y: Math.max(0, snap ? this.snap(y) : y),
                                };
                            });
                            this.saveNow();
                        },

                        saveNow() {
                            if (this.mode !== 'edit') return;
                            this.$wire.dispatch('planner.save-state', {
                                layout: toPlain(this.room),
                                tables: toPlain(this.tables),
                            });
                        },

                        get contentBounds() {
                            let maxRight = this.room.width;
                            let maxBottom = this.room.height;

                            for (const table of this.tables) {
                                maxRight = Math.max(maxRight, table.x + table.w + this.room.grid);
                                maxBottom = Math.max(maxBottom, table.y + table.h + this.room.grid);
                            }

                            return { w: maxRight, h: maxBottom };
                        },

                        get innerStyle() {
                            return `width:98%; min-width:${this.contentBounds.w}px; min-height:${this.contentBounds.h}px;`;
                        },
                    };
                });
            });
        </script>
    </div>
</div>
