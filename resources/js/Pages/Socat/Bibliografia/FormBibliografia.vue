<script setup>
import { ref, watch, defineProps, defineEmits, computed } from 'vue';
import { ElMessage } from 'element-plus';
import { Setting, Rank, RefreshLeft } from '@element-plus/icons-vue';

import GuardarButton from '@/Components/Biotica/GuardarButton.vue';
import BotonSalir from '@/Components/Biotica/SalirButton.vue';

const props = defineProps({
    accion: { type: String, required: true },
    biblioEdit: { type: Object, default: () => ({}) },
});

const emit = defineEmits(['cerrar', 'formSubmited']);
const mostrarModalOrden = ref(false);

const form = ref({
    IdBibliografia: null,
    Autor: '', Anio: '', TituloSubPublicacion: '', TituloPublicacion: '',
    EditoresCompiladores: '', EditorialPaisPagina: '', NumeroVolumenAnio: '', ISBNISSN: '',
});


const bibliografiaFormRef = ref(null); 

const rules = {
    Autor: [
        { required: true, message: 'El autor es obligatorio', trigger: 'blur' },
        { whitespace: true, message: 'No puede contener solo espacios', trigger: 'blur' },
        { pattern: /^(?!.*  ).+$/, message: 'No se permite más de un espacio seguido', trigger: ['blur', 'change'] }
    ],
    Anio: [
        { required: true, message: 'El año es obligatorio', trigger: 'blur' },
        { whitespace: true, message: 'No puede contener solo espacios', trigger: 'blur' },
        { pattern: /^(?!.*  ).+$/, message: 'No se permite más de un espacio seguido', trigger: ['blur', 'change'] }
    ],
    TituloPublicacion: [
        { required: true, message: 'El título es obligatorio', trigger: 'blur' },
        { whitespace: true, message: 'No puede contener solo espacios', trigger: 'blur' },
        { pattern: /^(?!.*  ).+$/, message: 'No se permite más de un espacio seguido', trigger: ['blur', 'change'] }
    ],
    camposOpcionales: [
        { pattern: /^(?!.*  ).+$/, message: 'No se permite más de un espacio seguido', trigger: ['blur', 'change'] },
        { whitespace: true, message: 'No puede enviar solo espacios', trigger: 'blur' }
    ]
};


const mapaIndices = {
    'Autor': '1',
    'Anio': '2',
    'TituloSubPublicacion': '3',
    'TituloPublicacion': '4',
    'EditoresCompiladores': '5',
    'NumeroVolumenAnio': '6',
    'EditorialPaisPagina': '7',
};

const ORDEN_ORIGINAL = [
    { id: 'Autor', label: 'Autor' },
    { id: 'Anio', label: 'Año' },
    { id: 'TituloSubPublicacion', label: 'Título de la subpublicación' },
    { id: 'TituloPublicacion', label: 'Título de la publicación' },
    { id: 'EditoresCompiladores', label: 'Editores / compiladores' },
    { id: 'NumeroVolumenAnio', label: 'Número, Volumen, Año' },
    { id: 'EditorialPaisPagina', label: 'Editorial, País, Página' },
    { id: 'ISBNISSN', label: 'ISBNISSN' },
];

const listaOrdenada = ref(ORDEN_ORIGINAL.map(item => ({ ...item })));

watch(() => props.biblioEdit?.IdBibliografia, (newId) => {
    if (props.biblioEdit && Object.keys(props.biblioEdit).length > 0) {
        form.value = { ...props.biblioEdit };

        if (props.biblioEdit.OrdenCitaCompleta) {
            const strOrden = props.biblioEdit.OrdenCitaCompleta.toString().trim();
            const mapaInverso = Object.fromEntries(Object.entries(mapaIndices).map(([k, v]) => [v, k]));

            let itemsReconstruidos = [];
            for (let char of strOrden) {
                const idKey = mapaInverso[char];
                const itemOriginal = ORDEN_ORIGINAL.find(i => i.id === idKey);
                if (itemOriginal) {
                    itemsReconstruidos.push({ ...itemOriginal });
                }
            }
            ORDEN_ORIGINAL.forEach(item => {
                if (!itemsReconstruidos.find(i => i.id === item.id)) {
                    itemsReconstruidos.push({ ...item });
                }
            });

            listaOrdenada.value = itemsReconstruidos;
        }
    }
}, { immediate: true });

const draggingIndex = ref(null);

const handleDragStart = (index) => {
    draggingIndex.value = index;
};

const handleDragOver = (index) => {
    if (draggingIndex.value === null || draggingIndex.value === index) return;
    const items = [...listaOrdenada.value];
    const draggedItem = items[draggingIndex.value];
    items.splice(draggingIndex.value, 1);
    items.splice(index, 0, draggedItem);
    listaOrdenada.value = items;
    draggingIndex.value = index;
};

const handleDragEnd = () => {
    draggingIndex.value = null;
};

const reiniciarOrden = () => {
    listaOrdenada.value = ORDEN_ORIGINAL.map(item => ({ ...item }));
};

const orden = computed(() => {
    const obj = {};
    listaOrdenada.value.forEach((item, index) => {
        obj[item.id] = index + 1;
    });
    return obj;
});

const construirReferencia = () => {
    const f = form.value;
    const bloques = {
        Autor: f.Autor || '',
        Anio: f.Anio ? `${f.Anio}` : '',
        TituloPublicacion: f.TituloPublicacion || '',
        TituloSubPublicacion: f.TituloSubPublicacion || '',
        NumeroVolumenAnio: f.NumeroVolumenAnio || '',
        EditorialPaisPagina: f.EditorialPaisPagina || '',
        EditoresCompiladores: f.EditoresCompiladores ? `${f.EditoresCompiladores}` : '',
        ISBNISSN: f.ISBNISSN ? `${f.ISBNISSN}` : ''
    };

    return listaOrdenada.value
        .map(item => bloques[item.id])
        .filter(val => val !== '')
        .join(' ');
};

const referenciaCompleta = computed(() => construirReferencia());

const submitForm = async () => {
    if (!bibliografiaFormRef.value) return;
    await bibliografiaFormRef.value.validate((valid, fields) => {
        if (valid) {
            const ordenString = listaOrdenada.value
                .map(item => mapaIndices[item.id])
                .join('');
            const formLimpio = {};
            Object.keys(form.value).forEach(key => {
                formLimpio[key] = typeof form.value[key] === 'string' 
                    ? form.value[key].trim() 
                    : form.value[key];
            });
            const datosParaEnviar = {
                ...formLimpio,
                OrdenCitaCompleta: ordenString,
                citaCompleta: referenciaCompleta.value
            };
            emit('formSubmited', datosParaEnviar);
        } 
    });
};

const guardarOrden = () => {
    mostrarModalOrden.value = false;
};

const cerrarDialogo = () => emit('cerrar');
const formTitle = computed(() => props.accion === 'crear' ? 'Insertar una nueva referencia bibliográfica' : 'Modificar la  referencia bibliográfica');
</script>

<template>
    <div class="dialog-header">
        <h3>{{ formTitle }}</h3>
    </div>

    <div class="header">
        <div class="dialog-body">
            <!-- IMPORTANTE: Añadimos :model y :rules -->
            <el-form ref="bibliografiaFormRef" :model="form" :rules="rules" label-position="top"
                class="bibliografia-form">

                <div class="form-actions" style="margin-top: -30px;">
                    <el-tooltip content="Configurar orden de referencia" placement="top">
                        <el-button type="info" circle @click="mostrarModalOrden = true"
                            style="width: 35px; height: 35px; font-size: 20px; background-color: blue; border:none;">
                            <el-icon>
                                <Setting />
                            </el-icon>
                        </el-button>
                    </el-tooltip>
                    <GuardarButton @click="submitForm" />
                    <BotonSalir accion="cerrar" @salir="cerrarDialogo" />
                </div>

                <!-- FILA 1: Autor y Año -->
                <el-row :gutter="20">
                    <el-col :span="12">
                        <el-form-item prop="Autor">
                            <template #label><span class="form-number">{{ orden.Autor }}</span> Autor(es)</template>
                            <el-input type="textarea" v-model="form.Autor" maxlength="255" show-word-limit
                                :autosize="{ minRows: 1, maxRows: 3 }" resize="none" placeholder="Autor(es)"></el-input>
                        </el-form-item>
                    </el-col>
                    <el-col :span="12">
                        <el-form-item prop="Anio">
                            <template #label><span class="form-number">{{ orden.Anio }}</span> Año(s)</template>
                            <el-input v-model="form.Anio" maxlength="50" show-word-limit placeholder="Año"></el-input>
                        </el-form-item>
                    </el-col>
                </el-row>

                <!-- FILA 2: Títulos (Corregido el cruce de props) -->
                <el-row :gutter="20">
                    <el-col :span="12">
                        <el-form-item prop="TituloPublicacion">
                            <template #label><span class="form-number">{{ orden.TituloPublicacion }}</span> Título de la
                                publicación</template>
                            <el-input type="textarea" v-model="form.TituloPublicacion" maxlength="255" show-word-limit
                                :autosize="{ minRows: 1, maxRows: 3 }" resize="none"
                                placeholder="Título principal"></el-input>
                        </el-form-item>
                    </el-col>
                    <el-col :span="12">
                        <el-form-item prop="TituloSubPublicacion" :rules="rules.camposOpcionales">
                            <template #label><span class="form-number">{{ orden.TituloSubPublicacion }}</span> Título de
                                la sub publicación</template>
                            <el-input type="textarea" v-model="form.TituloSubPublicacion" maxlength="255"
                                show-word-limit :autosize="{ minRows: 1, maxRows: 3 }" resize="none"
                                placeholder="Título capítulo"></el-input>
                        </el-form-item>
                    </el-col>
                </el-row>

                <!-- FILA 3: Volumen y Editorial -->
                <el-row :gutter="20">
                    <el-col :span="12">
                        <el-form-item prop="NumeroVolumenAnio" :rules="rules.camposOpcionales">
                            <template #label><span class="form-number">{{ orden.NumeroVolumenAnio }}</span> Número,
                                volumen, año, mes(es)</template>
                            <el-input type="textarea" v-model="form.NumeroVolumenAnio" maxlength="255" show-word-limit
                                :autosize="{ minRows: 1, maxRows: 3 }" resize="none"
                                placeholder="Datos revista"></el-input>
                        </el-form-item>
                    </el-col>
                    <el-col :span="12">
                        <el-form-item prop="EditorialPaisPagina" :rules="rules.camposOpcionales">
                            <template #label><span class="form-number">{{ orden.EditorialPaisPagina }}</span> Editorial,
                                país, lugar, páginas</template>
                            <el-input type="textarea" v-model="form.EditorialPaisPagina" maxlength="255" show-word-limit
                                :autosize="{ minRows: 1, maxRows: 3 }" resize="none"
                                placeholder="Datos editorial"></el-input>
                        </el-form-item>
                    </el-col>
                </el-row>

                <!-- FILA 4: Editores e ISBN -->
                <el-row :gutter="20">
                    <el-col :span="12">
                        <el-form-item prop="EditoresCompiladores" :rules="rules.camposOpcionales">
                            <template #label><span class="form-number">{{ orden.EditoresCompiladores }}</span>
                                Editor(es) / compilador(es)</template>
                            <el-input type="textarea" v-model="form.EditoresCompiladores" maxlength="255"
                                show-word-limit :autosize="{ minRows: 1, maxRows: 3 }" resize="none"
                                placeholder="Si aplica"></el-input>
                        </el-form-item>
                    </el-col>
                    <el-col :span="12">
                        <el-form-item prop="ISBNISSN" :rules="rules.camposOpcionales">
                            <template #label>ISBN/ISSN</template>
                            <el-input type="textarea" v-model="form.ISBNISSN" maxlength="50" show-word-limit
                                :autosize="{ minRows: 1, maxRows: 3 }" resize="none" placeholder="Si aplica">
                            </el-input>
                        </el-form-item>
                    </el-col>
                </el-row>

                <el-col :span="24"> <!-- Corregido span 25 a 24 que es el máximo -->
                    <el-form-item label="Referencia completa">
                        <el-input type="textarea" v-model="referenciaCompleta" :rows="5" readonly disabled></el-input>
                    </el-form-item>
                </el-col>
            </el-form>
        </div>
    </div>

    <el-dialog v-model="mostrarModalOrden" width="1000px" append-to-body class="custom-dialog-style">
        <template #header>
            <div class="header-oval modal-header-fix">
                <h3>Orden de la referencia</h3>
            </div>
        </template>
        <div class="header">
            <div class="modal-footer-btns">
                <el-tooltip content="Reiniciar orden" placement="bottom">
                    <el-button circle @click="reiniciarOrden"
                        style="width: 34px; height: 34px; font-size: 20px; margin-right: -10px; background-color: chartreuse;">
                        <el-icon>
                            <RefreshLeft />
                        </el-icon>
                    </el-button>
                </el-tooltip>

                <GuardarButton @click="guardarOrden" />
                <BotonSalir accion="cerrar" @salir="mostrarModalOrden = false" />
            </div>

            <div class="modal-inner">
                <div class="drag-zone">
                    <div v-for="(item, index) in listaOrdenada" :key="item.id" class="order-card"
                        :class="{ 'is-dragging': draggingIndex === index }" draggable="true"
                        @dragstart="handleDragStart(index)" @dragover.prevent="handleDragOver(index)"
                        @dragend="handleDragEnd">
                        <div class="card-left">
                            <span class="form-number">{{ index + 1 }}</span>
                            <span class="field-name">{{ item.label }}</span>
                        </div>
                        <el-icon class="drag-icon">
                            <Rank />
                        </el-icon>
                    </div>
                </div>

                <div class="preview-section-modal">
                    <label>VISTA PREVIA DEL FORMATO</label>
                    <div class="preview-text-modal">{{ referenciaCompleta }}</div>
                </div>
            </div>
        </div>


    </el-dialog>
</template>

<style scoped>
:deep(.el-dialog__body) {
    padding: 0 !important;
}

.dialog-header {
    background-color: #f5f5f5;
    padding: 20px 24px;
    border-bottom: 1px solid #e4e7ed;
    text-align: left;
    border-radius: 10px;
    margin-bottom: 10px;
}

.header {
    background-color: #ffffff;
    padding: 20px 24px;
    text-align: left;
    position: relative;
    z-index: 10;
    box-shadow: 0 2px 12px 0 rgba(0, 0, 0, 0.08);
    border-radius: 10px;
}

.dialog-header h3 {
    margin: 0;
    font-size: 1.25rem;
    font-weight: 600;
    color: #303133;
}

.dialog-body {
    padding: 30px;
}

.form-actions {
    display: flex;
    justify-content: flex-end;
    margin-top: 4px;
    margin-right: 35px;
    gap: 30px;
}


:deep(.el-form-item) {
    margin-bottom: 22px;
}

:deep(.el-form-item__label) {
    font-weight: 500 !important;
    padding-bottom: 4px !important;
    line-height: normal !important;
    font-size: 0.9em;
    color: #606266;
}


.form-number {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 22px;
    height: 22px;
    background-color: #409eff;
    color: white;
    border-radius: 50%;
    font-size: 12px;
    font-weight: bold;
    margin-right: 8px;
    line-height: 1;
    vertical-align: middle;
}

:deep(.el-form-item__label) {
    display: flex !important;
    align-items: center;
    font-weight: 600;
    color: #606266;
}

:deep(.el-textarea.is-disabled .el-textarea__inner) {
    background-color: #f8f9fa;
    color: #303133;
    cursor: default;
}



.orden-list {
    display: flex;
    flex-direction: column;
    gap: 10px;
}

.orden-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 12px 16px;
    background-color: #ffffff;
    border: 1px solid #e4e7ed;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.02);
    cursor: default;
}

.drag-handle {
    cursor: grab;
    margin-right: 15px;
    color: #909399;
    font-size: 18px;
}

.drag-handle:active {
    cursor: grabbing;
}

.ghost-item {
    opacity: 0.5;
    background: #ecf5ff;
    border: 1px dashed #409eff;
}

.field-info {
    display: flex;
    align-items: center;
}

.field-label {
    font-weight: 600;
    color: #303133;
}

.position-badge {
    background-color: #409eff;
    color: white;
    width: 28px;
    height: 28px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 6px;
    font-weight: bold;
    font-size: 14px;
}



.header-oval {
    background-color: #f0f2f5;
    padding: 18px 30px;
    border-radius: 12px;
    margin-bottom: 25px;
    margin-top: 30px;
    width: 100%;
}

.header-oval h3 {
    margin: 0;
    font-size: 1.15rem;
    font-weight: 600;
    color: #2c3e50;
}

.main-card {
    background: white;
    padding: 30px;
    border-radius: 15px;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05);
}

.top-actions {
    display: flex;
    justify-content: flex-end;
    gap: 15px;
    margin-top: -55px;
    margin-bottom: 25px;
}

.gear-btn {
    background-color: blue !important;
    border: none !important;
    width: 38px;
    height: 38px;
    font-size: 18px;
}

.form-number {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 22px;
    height: 22px;
    background-color: #409eff;
    color: white;
    border-radius: 50%;
    font-size: 11px;
    font-weight: bold;
    margin-right: 10px;
}

:deep(.el-form-item__label) {
    display: flex !important;
    align-items: center;
    font-weight: 600;
    color: #606266;
    padding-bottom: 8px !important;
}

.custom-dialog-style :deep(.el-dialog__header) {
    padding: 20px 25px 0 25px !important;
}

.modal-header-fix {
    margin-bottom: 0;
}

.modal-inner {
    padding: 10px 10px;
}

.instr {
    font-size: 0.85rem;
    color: #909399;
    margin-bottom: 20px;
}

.drag-zone {
    display: flex;
    flex-direction: column;
    gap: 10px;
    margin-bottom: 25px;
}

.order-card {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 14px 20px;
    background: #ffffff;
    border: 1px solid #e4e7ed;
    border-radius: 10px;
    cursor: grab;
    transition: all 0.2s;
}

.order-card:hover {
    border-color: #409eff;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
}

.is-dragging {
    opacity: 0.5;
    border-style: dashed;
    background: #f0f7ff;
}

.card-left {
    display: flex;
    align-items: center;
}

.field-name {
    font-weight: 600;
    color: #444;
    font-size: 0.95rem;
}

.drag-icon {
    color: #909399;
    font-size: 1.2rem;
}

/* VISTA PREVIA MODAL */
.preview-section-modal {
    border: 1px solid #e4e7ed;
    border-radius: 10px;
    overflow: hidden;
}

.preview-section-modal label {
    display: block;
    background: #f8f9fa;
    padding: 8px 15px;
    font-size: 10px;
    font-weight: 800;
    color: #909399;
    letter-spacing: 0.5px;
    border-bottom: 1px solid #e4e7ed;
}

.preview-text-modal {
    padding: 18px;
    font-family: 'Georgia', serif;
    font-style: italic;
    color: #333;
    line-height: 1.6;
}

.modal-footer-btns {
    display: flex;
    justify-content: flex-end;
    gap: 30px;
    margin-right: 40px;
    margin-bottom: 10px;
}
</style>