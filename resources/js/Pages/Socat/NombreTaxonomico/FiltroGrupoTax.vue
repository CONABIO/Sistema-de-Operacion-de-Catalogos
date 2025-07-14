<script setup>
    import { ref, onMounted, defineEmits } from 'vue';
    import arbolCheck from "@/Components/Biotica/ArbCheck.vue";
    import { ElMessageBox } from 'element-plus';
    import btnTraspaso from "@/Components/Biotica/BtnTraspaso.vue";
    import NotificacionExitoErrorModal from"@/Components/Biotica/NotificacionExitoErrorModal.vue";

    //Definición de variables 
    const props = defineProps({
                grupos: {
                    type: Object,
                    required: true,
                },
            });

    const propiedades = {
        children: 'children',
        label: 'label'
    }

    const notificacionVisible = ref(false);
    const notificacionTitulo = ref("");
    const notificacionMensaje = ref("");
    const notificacionTipo = ref("info");
    const notificacionDuracion = ref(5000);

    const datos =  props.grupos["original"];
    const checkAll = ref(false);
    const arbolRef = ref(null);
    const isIndeterminate = ref(false);

    const emit = defineEmits(['regresaGrupos', 'cerrar']);
    
    onMounted(() => {
        mostrarNotificacion(
            "Grupos taxonómicos",
            "Se debe seleccionar al menos un grupo taxonómico",
            "info",
            7000
        );
    });

    //Función referenciada para ejecutar las funciones desde el componente padre hasta el componente hijo
    const marcar = () => {
        if(arbolRef.value){
            arbolRef.value.marcarTodos();
        }
    }   

    const desmarcar = () => {
        if(arbolRef.value){
            arbolRef.value.desmarcarTodos();
        }
    }

    const recuperaMarcados = () => {
        if(arbolRef.value){
            arbolRef.value.recuperaMarcados();
        }
    }

    const recibeGrupos = (data) =>{
       if(data['ids'] !== '')
       {
        emit('cerrar', false);
        emit('regresaGrupos', data);
       }
       else{
            mostrarNotificacion(
                "Grupos taxonómicos",
                "Se debe selccionar al menos un grupo taxonómico",
                "error",
                7000
            );
       }
    }

    const mostrarNotificacion = (
        titulo,
        mensaje,
        tipo = "warning",
        duracion = 5000,
        dangerouslyUseHTML = false
    ) => {
        notificacionTitulo.value = titulo;
        notificacionMensaje.value = mensaje;
        notificacionTipo.value = tipo;
        notificacionDuracion.value = duracion;
        notificacionVisible.value = true;
    };

    const cerrarNotificacion = () => {
        notificacionVisible.value = false;
    };

</script>

<template>
    <div class="common-layout">
        <el-card>
            <el-container>
                <el-header class="header">
                    <h1 class="titulo">Catálogo de grupos taxonómicos</h1>
                </el-header>
                <el-main class="contenido">
                    <div>
                        <btnTraspaso @traspasa="recuperaMarcados()" />
                        <br />
                        <div v-show="!checkAll">
                            <el-checkbox :indeterminate="isIndeterminate" v-model="checkAll" @change="marcar">
                                Marcar todos
                            </el-checkbox>
                        </div>
                        <div v-show="checkAll">
                            <el-checkbox :indeterminate="isIndeterminate" v-model="checkAll" @change="desmarcar">
                                Desmarcar todos
                            </el-checkbox>
                        </div>
                    </div>
                    <arbolCheck :datosArbol="datos" 
                                :defaultProps="propiedades" 
                                ref="arbolRef"
                                @regresaMarcados="recibeGrupos"/>
                </el-main>
            </el-container>
        </el-card>
        <Teleport to="body">
            <NotificacionExitoErrorModal :visible="notificacionVisible" :titulo="notificacionTitulo"
                :mensaje="notificacionMensaje" :tipo="notificacionTipo" :duracion="notificacionDuracion"
                @close="cerrarNotificacion" />
        </Teleport>
    </div>
</template>

<style scoped>
    .common-layout {
        display: flex;
        flex-direction: column;
        min-height: 100vh;
    }

    .header {
        text-align: center;
        padding: 0.5rem; /* Reducir el padding en móviles */
        background-color: #f5f5f5;
        border-bottom: 1px solid #ddd;
    }

    .titulo {
        font-size: 1.5rem;
        font-weight: bold;
        margin-bottom: 0.5rem; /* Añadir margen inferior para separar del contenido */
    }

    .contenido {
        max-width: 100%;
        padding: 0.5rem; /* Reducir el padding en móviles */
        margin-top: 1rem; /* Añadir margen superior para separar del título */
    }

    /* Estilos para el botón y el checkbox */
    .contenido > div {
        display: flex;
        flex-direction: column; /* Colocar elementos en vertical */
        align-items: flex-start; /* Alinear a la izquierda */
        gap: 0.5rem; /* Espacio entre elementos */
    }

    /* Responsividad */
    @media (min-width: 768px) {
        .titulo {
            font-size: 2rem;
        }
    }

    @media (max-width: 480px) {
        .titulo {
            font-size: 1rem; /* Reducir el tamaño de la fuente en móviles */
        }

        .header {
            padding: 0.25rem; /* Reducir el padding en móviles */
        }

        .contenido {
            padding: 0.25rem; /* Reducir el padding en móviles */
            margin-top: 0.5rem; /* Ajustar margen superior en móviles */
        }
    }
</style>