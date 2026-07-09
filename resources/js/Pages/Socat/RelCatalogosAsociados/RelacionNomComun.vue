<template>
    <div>
        <el-card class="box-card">
            <div class="common-layout">
                <el-container style="height: 72vh;">
                    <el-header class="header">
                        <div class="header-content">
                            <h1 class="titulo">Asociación taxón nombre comun</h1>
                        </div>
                    </el-header>
                    <el-main style="padding: 15px; background: #fff; overflow: hidden;">
                        Boton de traspaso
                        <div style="height: 650px; box-shadow: var(--el-border-color-light) 0px 0px 10px">
                                <el-splitter lazy>
                                    <el-splitter-panel min="50">
                                        <!--TablaFiltrable ref="tablaRef" class="flex-grow" 
                                        :columnas="columnasDefinidasNomCom" 
                                        v-model:datos="tablaNomComun" 
                                        :row-class-name="tableRowClassName"           
                                        v-model:total-items="contRegNomCom" 
                                        endpoint="/busca-nombre-comun" 
                                        id-key="IdNomComun" 
                                        @row-click="manejarClickFila"  
                                        :highlight-current-row="false"  
                                        @editar-item="editarNombreComun" 
                                        @eliminar-item="eliminarNombreComun" 
                                        @nuevo-item="nuevoNombreComun"
                                        @cerrar="cerrarDialogo">

                                        <template #expand-column>
                                            <el-table-column type="expand">
                                                <template #default="{ row }">
                                                    <div class="expand-content-detail">
                                                        <p><strong>IdNomComun:</strong> {{ row.IdNomComun }}</p>
                                                        <p><strong>IdOriginal:</strong> {{ row.IdOriginal }}</p>
                                                        <p><strong>Catalogo:</strong> {{ row.Catalogo }}</p>
                                                        <p><strong>FechaCaptura:</strong> {{ row.FechaCaptura }}</p>
                                                        <p><strong>FechaModificacion:</strong> {{ row.FechaModificacion }}</p>
                                                    </div>
                                                </template>
                                            </el-table-column>
                                        </template>
                                    </TablaFiltrable-->
                                    <TablaFiltrable 
                                        :columnas = "columnasDefinidasNomCom" 
                                        :datos = "tablaNomComun"
                                        :opciones-filtro = "opcionesFiltroRegCaract"
                                        :totalItems = "contRegNomCom"
                                        :itemsPerPage = 5
                                        :mostrarBiblio = "true"
                                        :mostrarAcci = "false"
                                        :alturaTabla = 304
                                        :highlight-current-row = "true"
                                        :mostrarNuevo = "false"
                                        :mostrarEditar = "false"
                                        :mostrarBorrar = "false"
                                        :mostrarSalir = "false">                                                
                                    </TablaFiltrable>
                                    </el-splitter-panel>
                                    <el-splitter-panel min="50">
                                        <div style="height: 650px; box-shadow: var(--el-border-color-light) 0px 0px 10px">
                                            <el-splitter layout="vertical">
                                            <el-splitter-panel>
                                                1
                                            </el-splitter-panel>
                                            <el-splitter-panel>
                                                2
                                            </el-splitter-panel>
                                            </el-splitter>
                                        </div>
                                    </el-splitter-panel>
                                </el-splitter>
                        </div>
                    </el-main>
                </el-container>
            </div>
        </el-card>
    </div>
</template>
<script setup>
    import { onMounted, ref, watch } from 'vue';
    import TablaFiltrable from "@/Components/Biotica/TablaFiltrable.vue";

    const tablaNomComun = ref ([]);
    const contRegNomCom = ref(0);

    const columnasDefinidasNomCom = ref([
        { prop: 'NomComun', label: 'Nombre común', minWidth: '120', 
          sortable: true, filtrable: true, align: 'left' },
        { prop: 'Lengua', label: 'Lengua', minWidth: '150', 
          sortable: true, filtrable: true, align: 'left' },
        { prop: 'Observaciones', label: 'Observaciones', minWidth: '150', 
          sortable: true, filtrable: true, align: 'left' },
    ]);

    onMounted( async () => {
        const respNomCom = await axios.get('/cargaCatNomComun');

        if(respNomCom.status === 200)
        {
            tablaNomComun.value = respNomCom.data;
            contRegNomCom.value = respNomCom.data.length;
        }
        console.log("Esta es la carga inicial de Nomcomun: ", respNomCom);
    })
</script>