<script setup>
import { ref, watch, nextTick, computed, onMounted } from 'vue';
import DialogGeneral from '@/Components/Biotica/DialogGeneral.vue';
import GuardarButton from "@/Components/Biotica/GuardarButton.vue";
import { ElMessage } from 'element-plus';
import BotonSalir from '@/Components/Biotica/SalirButton.vue';
import axios from 'axios';
import { Search } from '@element-plus/icons-vue';

const props = defineProps({
    visible: Boolean,
    accion: String,
    objetoExternoEdit: Object,
});

const emit = defineEmits(['cerrar', 'formSubmited']);

const inputFileRef = ref(null);
const dialogVisible = ref(false);
const formRef = ref(null);
const selectedOption = ref('localFile');
const form = ref({
    IdMime: null,
    NombreObjeto: '',
    NombreSitio: '',
    Ruta: '',
    Protocolo: 'HTTP',
    Usuario: '',
    Password: '',
    UnidadLogica: '',
    Titulo: '',
    Autor: '',
    Institucion: '',
    Fecha: null,
    Observaciones: '',
    UrlExterna: '',
});

const abrirExploradorArchivos = () => {
    if (inputFileRef.value) {
        inputFileRef.value.click();
    }
};

const manejarSeleccionArchivo = (event) => {
    const archivos = event.target.files;
    if (archivos.length > 0) {
        const archivo = archivos[0];
        form.value.NombreObjeto = archivo.name;
        const partesNombre = archivo.name.split('.');
        if (partesNombre.length > 1) {
            const extension = partesNombre.pop().toLowerCase();
            const tipoEncontrado = opcionesTipoArchivo.value.find(
                opt => opt.Extension.toLowerCase() === extension
            );
            if (tipoEncontrado) {
                form.value.IdMime = tipoEncontrado.IdMime;
                ElMessage.success(`Tipo de archivo '${extension.toUpperCase()}' auto-seleccionado.`);
            } else {
                ElMessage.warning(`La extensión '${extension.toUpperCase()}' no se encontró.`);
            }
        }
    }
};

const opcionesProtocolo = ref(['HTTP', 'HTTPS', 'FTP', 'FILE']);
const opcionesTipoArchivo = ref([]);

const rules = computed(() => {
    const baseRules = {
        IdMime: [{ required: true, message: 'El tipo de archivo es obligatorio', trigger: 'change' }],
    };

    if (selectedOption.value === 'localFile') {
        baseRules.NombreObjeto = [{ required: true, message: 'El nombre del archivo es obligatorio', trigger: 'blur' }];
    } else {
        baseRules.UrlExterna = [{ required: true, message: 'La URL externa es obligatoria', trigger: 'blur' }];
    }
    return baseRules;
});

const dialogTitle = computed(() => {
    return props.accion === 'crear' ? 'Ingresar un nuevo objeto externo' : 'Modificar el objeto externo seleccionado';
});

const cargarTiposArchivo = async () => {
    try {
        const response = await axios.get('/api/mimes');
        opcionesTipoArchivo.value = response.data;
    } catch (error) {
        console.error("Error al cargar los tipos de archivo:", error);
        ElMessage.error('No se pudieron cargar los tipos de archivo.');
    }
};

onMounted(() => {
    cargarTiposArchivo();
});

watch(() => props.visible, (newVal) => {
    dialogVisible.value = newVal;
    if (newVal) {
        if (props.accion === 'editar' && props.objetoExternoEdit) {
            form.value = { ...props.objetoExternoEdit };
            if (form.value.UrlExterna) {
                selectedOption.value = 'webPage';
            } else {
                selectedOption.value = 'localFile';
            }
        } else {
            form.value = {
                IdMime: null,
                NombreObjeto: '',
                NombreSitio: '',
                Ruta: '',
                Protocolo: 'HTTP',
                Usuario: '',
                Password: '',
                UnidadLogica: '',
                Titulo: '',
                Autor: '',
                Institucion: '',
                Fecha: null,
                Observaciones: '',
                UrlExterna: '',
            };
            selectedOption.value = 'localFile';
        }
        nextTick(() => {
            formRef.value?.clearValidate();
        });
    }
}, { immediate: true });

watch(dialogVisible, (newVal) => {
    if (!newVal) {
        emit('cerrar');
    }
});

watch(selectedOption, (newVal) => {
    if (newVal === 'localFile') {
        form.value.UrlExterna = '';
        form.value.NombreSitio = '';
        form.value.Ruta = '';
        form.value.Protocolo = 'HTTP'; 
        form.value.NombreObjeto = '';
        form.value.UnidadLogica = '';
        form.value.Usuario = '';
        form.value.Password = '';
    }
    nextTick(() => {
        formRef.value?.clearValidate();
    });
});

watch(() => form.value.UrlExterna, (newUrl) => {
    if (newUrl && selectedOption.value === 'webPage') {
        try {
            const url = new URL(newUrl);
            const protocol = url.protocol.replace(':', '').toUpperCase();
            if (opcionesProtocolo.value.includes(protocol)) {
                form.value.Protocolo = protocol;
            }
            form.value.NombreSitio = url.hostname;
            const pathParts = url.pathname.split('/').filter(p => p); 
            if (pathParts.length > 0) {
                form.value.Ruta = `/${pathParts[0]}`;
                const remainingPath = pathParts.slice(1).join('/');
                form.value.NombreObjeto = remainingPath + url.search;
            } else {
                form.value.Ruta = '/';
                form.value.NombreObjeto = url.search; 
            }
            const htmlFileType = opcionesTipoArchivo.value.find(
                opt => opt.Extension.toLowerCase() === 'html' || opt.MIME.toLowerCase() === 'htmlfile'
            );
            if (htmlFileType) {
                form.value.IdMime = htmlFileType.IdMime;
            }
        } catch (error) {
            console.warn('URL inválida, esperando a que sea completa:', error.message);
            form.value.Protocolo = 'HTTP';
            form.value.NombreSitio = '';
            form.value.Ruta = '';
            form.value.NombreObjeto = ''; 
        }
    }
});

const intentarGuardar = async () => {
    if (!formRef.value) return;

    const isValid = await formRef.value.validate();
    if (isValid) {
        const datosParaEnviar = {
            ...form.value,
            IdObjetoExterno: props.accion === 'editar' ? props.objetoExternoEdit?.IdObjetoExterno : null,
        };
        emit('formSubmited', datosParaEnviar);
    } else {
        ElMessage.error('Por favor, corrija los errores en el formulario.');
    }
};

const cerrarDialogo = () => {
    emit('cerrar');
};
</script>

<template>
    <DialogGeneral v-model="dialogVisible" :bot-cerrar="true" :press-esc="true">
        <div class="dialog-header">
            <h3>{{ dialogTitle }}</h3>
        </div>
        <div class="header-form">
            <div class="form-actions">
                <GuardarButton @click="intentarGuardar" />
                <BotonSalir accion="cerrar" @salir="cerrarDialogo" />
            </div>
            <div class="dialog-body">
                <el-form :model="form" ref="formRef" :rules="rules" label-position="top">

                    <el-form-item label="Origen del objeto" style="margin-bottom: 20px; margin-top: -75px;">
                        <el-radio-group v-model="selectedOption">
                            <el-radio label="localFile">Archivo local</el-radio>
                            <el-radio label="webPage">Página web (URL)</el-radio>
                        </el-radio-group>
                    </el-form-item>

                    <div v-if="selectedOption === 'localFile'">
                        <el-form-item label="Buscar archivo" prop="NombreObjeto">
                            <el-input placeholder="Haga clic en la lupa para seleccionar un archivo local"
                                :model-value="form.NombreObjeto" readonly>
                                <template #append>
                                    <el-button :icon="Search" @click="abrirExploradorArchivos" />
                                </template>
                            </el-input>
                            <input type="file" ref="inputFileRef" @change="manejarSeleccionArchivo"
                                style="display: none;" />
                        </el-form-item>
                        <el-form-item label="Nombre del archivo" prop="NombreObjeto">
                            <el-input v-model="form.NombreObjeto" placeholder="nombre.extension" />
                        </el-form-item>
                    </div>


                    <el-form-item label="URL de la página web" prop="UrlExterna" v-else>
                        <el-input v-model="form.UrlExterna" placeholder="Ej: https://www.ejemplo.com/pagina" />
                    </el-form-item>

                    <el-row :gutter="20">
                        <el-col :span="12">
                            <el-form-item label="Protocolo" prop="Protocolo">
                                <el-select v-model="form.Protocolo" placeholder="Seleccione" style="width: 100%;"
                                    :disabled="selectedOption === 'localFile'">
                                    <el-option v-for="item in opcionesProtocolo" :key="item" :label="item"
                                        :value="item" />
                                </el-select>
                            </el-form-item>
                        </el-col>
                        <el-col :span="12">
                            <el-form-item label="Unidad lógica" prop="UnidadLogica">
                                <el-input v-model="form.UnidadLogica" placeholder="Ej: c, d, etc."
                                    :disabled="selectedOption === 'webPage'" />
                            </el-form-item>
                        </el-col>
                    </el-row>

                    <el-form-item v-if="selectedOption === 'webPage'" label="Nombre del archivo" prop="NombreObjeto">
                        <el-input v-model="form.NombreObjeto" placeholder="Auto-generado desde la URL" readonly />
                    </el-form-item>

                    <el-form-item label="Nombre del sitio" prop="NombreSitio">
                        <el-input v-model="form.NombreSitio" placeholder="www.ejemplo.com"
                            :disabled="selectedOption === 'localFile'" />
                    </el-form-item>

                    <el-form-item label="Ruta" prop="Ruta">
                        <el-input v-model="form.Ruta" placeholder="/carpetas/adicionales"
                            :disabled="selectedOption === 'webPage'" />
                    </el-form-item>

                    <el-row :gutter="20">
                        <el-col :span="12">
                            <el-form-item label="Tipo de archivo" prop="IdMime">
                                <el-select v-model="form.IdMime" placeholder="Seleccione un tipo" style="width: 100%;">
                                    <el-option v-for="item in opcionesTipoArchivo" :key="item.IdMime"
                                        :label="`${item.Extension} - ${item.MIME}`" :value="item.IdMime" />
                                </el-select>
                            </el-form-item>
                        </el-col>
                        <el-col :span="6">
                            <el-form-item label="Usuario" prop="Usuario">
                                <el-input v-model="form.Usuario" disabled />
                            </el-form-item>
                        </el-col>
                        <el-col :span="6">
                            <el-form-item label="Contraseña" prop="Password">
                                <el-input v-model="form.Password" type="password" show-password disabled />
                            </el-form-item>
                        </el-col>
                    </el-row>

                    <el-form-item label="Observaciones" prop="Observaciones">
                        <el-input v-model="form.Observaciones" type="textarea" :rows="3" />
                    </el-form-item>

                    <el-divider content-position="center">Cita del objeto externo</el-divider>

                    <el-form-item label="Título" prop="Titulo">
                        <el-input v-model="form.Titulo" />
                    </el-form-item>

                    <el-form-item label="Institución" prop="Institucion">
                        <el-input v-model="form.Institucion" />
                    </el-form-item>

                    <el-row :gutter="20">
                        <el-col :span="12">
                            <el-form-item label="Autor" prop="Autor">
                                <el-input v-model="form.Autor" />
                            </el-form-item>
                        </el-col>
                        <el-col :span="12">
                            <el-form-item label="Fecha de creación" prop="Fecha">
                                <el-date-picker v-model="form.Fecha" type="date" placeholder="Seleccione una fecha"
                                    style="width: 100%;" />
                            </el-form-item>
                        </el-col>
                    </el-row>
                </el-form>
            </div>
        </div>
    </DialogGeneral>
</template>

<style scoped>
:deep(.el-dialog__body) {
    padding: 0 !important;
}

.dialog-header {
    background-color: #f1f7ff;
    padding: 20px 24px;
    border-bottom: 1px solid #e4e7ed;
    text-align: left;
    border-radius: 10px;
    margin-bottom: 10px;
}

.header-form {
    background-color: #f3f3f3;
    padding: 20px 24px;
    border: 3px;
    text-align: left;
    border-radius: 10px;
    background-color: #ffffff;
    padding: 20px 24px;
    text-align: left;
    position: relative;
    z-index: 10;
    box-shadow: 0 2px 12px 0 rgba(0, 0, 0, 0.08);
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
    margin-bottom: 20px;
    gap: 10px;
}

:deep(.el-form-item) {
    margin-bottom: 18px;
}

:deep(.el-form-item__label) {
    padding-bottom: 4px;
    line-height: normal;
    font-size: 0.9em;
    color: #606266;
}

.el-divider__text {
    font-weight: 600;
    color: #303133;
}
</style>