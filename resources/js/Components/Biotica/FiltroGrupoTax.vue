<template>
    <el-col style="padding: 8px; flex: 1;">
        <el-row :gutter="16" style="margin: 0 10px;">
            <el-col :span="11" style="padding: 8px;">
                <div style="margin-left: 8px;"> 
                    <span class="demo-input-label">Catálogo(s)</span>
                    <el-input type="textarea" :rows="2" placeholder="Catálogos" v-model="catalogos" :disabled="true">
                    </el-input>
                </div>
            </el-col>
            <el-col :span="11" style="padding: 8px;">
                <div style="margin-left: 8px;">
                    <span class="demo-input-label">Grupo SCAT</span>
                    <el-input type="textarea" :rows="2" placeholder="Grupo SCAT" v-model="grupos" :disabled="true">
                    </el-input>
                </div>
            </el-col> 
            <el-col :span="2" style="padding: 8px;display: flex; align-items: center; justify-content: center;">
            <el-tooltip class="item" 
                        effect="dark" 
                        content="Catálogo de Grupos taxonómicos" 
                        placement="bottom">
                    <el-button @click="filtro_Catalogos()" 
                            type="primary" 
                            circle 
                            color="#5bb1f0">
                        <el-icon><Conectado /></el-icon>
                    </el-button>
                </el-tooltip>
            </el-col> 
        </el-row>
    </el-col>

    <DialogForm v-model="dialogFormVisibleCat" :botCerrar="false" :pressEsc="false">
        <FiltroGrupos :grupos="gruposTax" @cerrar="cerrarDialog" @regresaGrupos="recibeGrupos" />
    </DialogForm>

</template>
<script setup>
    import {ref, onMounted, watchEffect, defineEmits, watch} from 'vue';
    import Conectado from '@/Components/Biotica/Icons/Conectado.vue';
    import DialogForm from '@/Components/Biotica/DialogGeneral.vue';
    import FiltroGrupos from '@/Pages/Socat/NombreTaxonomico/FiltroGrupoTax.vue';

    //Se definen los componentes que se emiten al padre
    const emit = defineEmits(['enviagrupos']);
    
    const dialogFormVisibleCat = ref(false);
    const catalogos = ref('');
    const grupos = ref('');
    const idsGrupos = ref('');

    const props = defineProps({
        gruposTax: {
            type: Object,
            required: true,
        },
        catalogPadre: {
            type: String,
            required: true,
        },
        gruposPadre: {
            type: String,
            required: true,
        },
        idsGruposPadre: {
            type: String,
            required: true
        },
    });

    function emitirDatos() {
        emit('enviagrupos', {
            catalogos: catalogos.value,
            grupos: grupos.value,
            idsGrupos: idsGrupos.value
        });
    }

    //Declaración de Funciones
    const filtro_Catalogos = () => {
        //Funcion para abrir el modal que muestra los catalogos taxonómicos
        dialogFormVisibleCat.value = true;
    };

    const cerrarDialog = (valor) => {
        dialogFormVisibleCat.value = valor; // Cambia la visibilidad del diálogo
    };

    //Funcion para recibir los datos de los grupos y catalogos selccionados
    const recibeGrupos = async (data) => {
        catalogos.value = data['catalogos'];
        grupos.value = data['grupos'];
        idsGrupos.value = data['ids'];
    };

    // Función para cargar las variables del componente padre en el hijo 
    const cargaGrupos = async () => {
        catalogos.value = props.catalogPadre;
        grupos.value = props.gruposPadre;
        idsGrupos.value = props.idsGruposPadre;
    };

    onMounted(async () => {
        console.log("Ya estoy aqui");
        
        cargaGrupos();
    });

    watch([catalogos, grupos, idsGrupos], () => {
        emitirDatos();
    });

    watchEffect(() => {
        if (props.gruposPadre) {
            cargaGrupos();
        }
    });

</script>