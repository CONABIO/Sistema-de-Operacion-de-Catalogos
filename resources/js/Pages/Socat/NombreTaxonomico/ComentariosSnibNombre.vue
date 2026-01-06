<template>
    <div class="common-layout">
        <el-card>
            <el-container style="height: 500px; border: 1px solid #eee">
                <el-header class="header">
                    <div class=" header-content ">
                        <h1 class="titulo">Comentarios al taxón en el SNIB</h1>
                    </div>
                    <div class =" form-actions ">
                        <BotonSalir accion="cerrar" @salir="cerrarDialogo"/>
                    </div>
                </el-header>
                <el-main class="contenido" style="text-align: right; 
                            font-size: 12px">
                    <TablaFiltrable 
                        :columnas="columnasDefinidas" 
                        :datos="comSnib"
                        :totalItems = "totalComSnib"
                        :itemsPerPage="10">
                        <template #expand-column>
                            <el-table-column type="expand">
                                <template #default="{ row }">
                                <div class="expand-content-detail">                                  
                                    <el-table :data="row.Detalle"
                                    size="small"
                                    :border="false"
                                    :show-header="true"
                                    class="compact-table">
                                        <el-table-column label="Llave nombre" prop="llavenombre" header-align="left" align="left"/>
                                        <el-table-column label="Comentario SNIB" prop="comentarioscat" header-align="left" align="left"/>
                                    </el-table>
                                </div>
                                </template>
                            </el-table-column>
                        </template>
                    </TablaFiltrable>
                </el-main>
            </el-container>
        </el-card>
    </div>
</template>
<script setup>

    import { ref, onMounted, watch } from 'vue';
    import { ChatDotSquare } from '@element-plus/icons-vue';
    import comentarioSnib from '@/Components/Biotica/Icons/Comentarios.vue';
    import BotonSalir from '@/Components/Biotica/SalirButton.vue';
    import { ElLoading } from 'element-plus';
    import TablaFiltrable from "@/Components/Biotica/TablaFiltrableImg.vue";

    const props = defineProps({
        taxon: {
            type: Object
        },
    });

    const emit = defineEmits(['cerrar']);

    const comSnib = ref([]);
    const totalComSnib = ref(0);
    const columnasDefinidas = ref([
        {
            prop: 'idnombre', label: 'IdNombre', minWidth: '80',
            align: 'left', tipo: 'Texto', filtrable: false
        },
        {
            prop: 'comentarioscat', label: 'Comentarios SCAT', minWidth: '80',
            align: 'left', tipo: 'Texto', filtrable: false
        },
        {
            prop: 'Conteo', label: 'Conteo', minWidth: '55', align: 'left',
            tipo: 'Texto', filtrable: false
        }
    ]);
    const totalComDet = ref(0);
    const columnasDefDet = ref([
        {
            prop: 'comentarioscat', label: 'Comentarios SCAT', minWidth: '80',
            align: 'left', tipo: 'Texto', filtrable: false
        },
        {
            prop: 'llavenombre', label: 'Llave Nombre', minWidth: '80',
            align: 'left', tipo: 'Texto', filtrable: false
        }
    ]);

    const tableDetCom = ref([]);

    const cerrarDialogo = () => {
        emit('cerrar');
    };

    watch(
        ()=> props.taxon,
        (newValue, oldValue) => {
            carga_AcumSnib();
            comSnib.value=[];
            totalComSnib.value = 0
            tableDetCom.value=[];
        },
        //carga_inicio(),
        { deep: true, inmediate: true }
    );

    onMounted(() => {
        try {
            carga_AcumSnib();
        } catch (error) {
            console.error('Error al obtener el usuario: ', error);
        }
    });

    const carga_AcumSnib = async () =>{
       
        const loading = ElLoading.service({
        lock: true,
        text: "Loading",
        spinner: `<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 200 200"><path fill="none" d="M0 0h200v200H0z"></path><path fill="none" stroke-linecap="round" stroke="#53B0FF" stroke-width="15" transform-origin="center" d="M70 95.5V112m0-84v16.5m0 0a25.5 25.5 0 1 0 0 51 25.5 25.5 0 0 0 0-51Zm36.4 4.5L92 57.3M33.6 91 48 82.7m0-25.5L33.6 49m58.5 33.8 14.3 8.2"><animateTransform type="rotate" attributeName="transform" calcMode="spline" dur="1.1" values="0;-120" keyTimes="0;1" keySplines="0 0 1 1" repeatCount="indefinite"></animateTransform></path><path fill="none" stroke-linecap="round" stroke="#53B0FF" stroke-width="15" transform-origin="center" d="M130 155.5V172m0-84v16.5m0 0a25.5 25.5 0 1 0 0 51 25.5 25.5 0 0 0 0-51Zm36.4 4.5-14.3 8.3M93.6 151l14.3-8.3m0-25.4L93.6 109m58.5 33.8 14.3 8.2"><animateTransform type="rotate" attributeName="transform" calcMode="spline" dur="1.1" values="0;120" keyTimes="0;1" keySplines="0 0 1 1" repeatCount="indefinite"></animateTransform></path></svg>`,
        backgroud: 'rgba(255,255,255,0.85)',
      });

        const  params = {
            idNombre: props.taxon.id
        };

        const response = await axios.get('/carga-AcumuladoSnib', { params } );

        if (response.status === 200) {
            comSnib.value = response.data;
            totalComSnib.value = comSnib.value.length;
            console.log("Este es el resultado de la consulta: ", comSnib.value);
        }
        else {
            console.log("Se presentó un error en la recuperación de los datos");
        }

        loading.close();
    };

</script>
<style scoped>
    .common-layout {
        display: flex;
        flex-direction: column;
        min-height: 80vh;
    }

    .header {
          background-color: #d9e1eb;
          padding: 15px;
          border-bottom: 1px solid #e0e0e0;
          height: auto !important;
          min-height: auto !important;
          display: flex;
          align-items: center;
          justify-content: center;
          flex-shrink: 0;
          border-radius: 8px;
          color: white;
    }

    .header-content {
        display: flex;
        justify-content: space-between;
        align-items: center;
        width: 100%;
    }

    .titulo {
        font-size: 1.25rem;
        font-weight: bold;
        color: #333;
        margin: 0;
        text-align: center;
    }

    .contenido {
        max-width: 100%;
        padding: 0.5rem; /* Reducir el padding en móviles */
        margin-top: 1rem; /* Añadir margen superior para separar del título */
    }

    /*-----------------------------
    .expand-content-detail .el-table__row > td.el-table__cell {
        padding-top: 4px;
        padding-bottom: 4px;
        border-bottom: none;
    }

    .expand-content-detail .el-table th.el-table__cell {
        border-bottom: none;
        text-align: left;
    }

    .expand-content-detail .el-table::before {
        display: none;
    }*/


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