<template>
  <div>
    <el-card class="box-card">
      <div class="common-layout">
        <el-container style="height: 90vh;">
          <el-header style="background: #f5f5f5; padding: 10px; flex-shrink: 0;">
            <el-row :gutter="10" align="middle">
              <h2 class="titulo">Citas bibliograficas asociadas</h2>
            </el-row>
          </el-header>
            <el-main style="padding: 15px; background: #fff; overflow: hidden;">
                <el-row>
                    <el-card class="main-content-card">
                        <div class="dual-panel-container">
                            <el-card class="table-panel">
                                <span style="font-size: 20px; font-weight: bold;">
                                    Nombres cientificos asociados a:
                                </span>
                                <br/>
                                <span style="font-size: 18px; color: #8A2815; font-weight: bold;">
                                    {{ props.taxonAct.label }}
                                </span>
                                <br/>
                                <br/>
                                <span style="font-size: 18px; color: #2A661E; font-weight: bold;">
                                    {{ tipRelacion }}
                                </span>
                                <br/>
                                <div style="flex: 1; overflow-y: auto; border: 1px solid #dcdfe6; border-radius: 4px; margin-top: 10px;">
                                    <TablaFiltrable :container-class="'main-section'" :columnas="columnasDefinidas"
                                        v-model:datos = "props.relaciones" v-model:total-items="props.totalRegistros"
                                        :opciones-filtro = "opcionesFiltroNomenclatura"
                                        :origen = "true"
                                        @row-click = "manejaClick">
                                        <template #expand-column>
                                            <el-table-column type="expand">
                                                <template #default="{ row }">
                                                    <div class="expand-content-detail">
                                                    <p><strong>Fecha de alta:</strong>{{ row.FechaCaptura }}</p>
                                                    <p><strong>Fecha de modificación:</strong>{{ row.FechaModificacion }}</p>
                                                    </div>
                                                </template>
                                            </el-table-column>
                                        </template>
                                    </TablaFiltrable>
                                </div>
                            </el-card>
                            <el-card class="table-panel">
                                <span style="font-size: 20px; font-weight: bold;">
                                    Cita(s) bibliografica(s) asociada(s) a:
                                </span>
                            </el-card>
                        </div>
                    </el-card>
                </el-row>
            </el-main>
        </el-container>
      </div>
    </el-card>
  </div>
</template>
<script setup>
    import { ref } from 'vue';
    import TablaFiltrable from "@/Components/Biotica/TablaFiltrableImg.vue";

    // Props del componente
    const props = defineProps({
        taxonAct: {
            type: Object
        },
        relaciones: {
            type: Object,
            required: true,
        }, 
        totalRegistros:{
            type: Number,
            required:true, 
            default: 0
        }
    });

    const totalRegNom = ref(0);
    const tipRelacion = ref("");

    const columnasDefinidas = ref([
    {
        prop: 'TipoRelacion', label: 'Tipo Relación', minWidth: '190', sortable: true,
        align: 'left', tipo: 'imagenTexto', filtrable: true
    },
    {
        prop: 'Nombrecompleto', label: 'Nombre Completo', minWidth: '230', sortable: true,
        align: 'left', tipo: 'imagenTexto', filtrable: true
    },
    {
        prop: 'Biblio', label: 'Ref.', minWidth: '90', sortable: false, align: 'center',
        tipo: 'imagenTexto', filtrable: false
    }
    ]);


    const manejaClick = (row) => {
        /*observacionesRel.value = row.Observaciones;
        relacionAct.value = row;

        if(JSON.stringify(relDetectada.value) != JSON.stringify(row))
        {
            habObservaciones.value = true;
        }

        if(row.TipoRelacion.idTipoRel === 1 || row.TipoRelacion.idTipoRel === 2){
            habCambioSinBas.value = false;
        }else{
            habCambioSinBas.value = true;
        }*/
       tipRelacion.value = row.TipoRelacion.texto
       console.log("Este es el row: ", row.TipoRelacion.texto);
    }

</script>
<style scope>
    .titulo {
        font-size: 1.5rem;
        font-weight: bold;
        margin: 0;
    }

    .main-content-card {
        width: 100%;
        height: 100%;
        border-radius: 8px;
        box-shadow: 0 2px 12px rgba(0, 0, 0, 0.07);
        padding: 15px;
    }

    .dual-panel-container {
        display: flex;
        gap: 12px;
        width: 100%;
        height: 60vh;
    }

    .table-panel {
        flex: 1;
        min-width: 400px;
        display: flex;
        flex-direction: column;
    }
</style>