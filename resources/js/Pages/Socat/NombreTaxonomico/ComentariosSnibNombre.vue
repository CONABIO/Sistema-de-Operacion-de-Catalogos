<template>
     <div>
        <el-container style="height: 300px; border: 1px solid #eee">
            <el-header style="text-align: right; font-size: 12px">
                <el-table  :data="comSnib"
                           :highlight-current-row= true
                           :border="true"
                           height="400"
                           style="width: 100%"
                           @row-click="carga_DetCom">
                    <div slot="empty" v-if="comSnib.length=='0'">

                    </div>
                    <el-table-column
                        v-for="(column) in tableColumns"
                        :key="column.label"
                        :label="column.label"
                        :prop="column.prop"
                        :column-key="column.prop"
                        :min-width="column.minWidth"
                        :sortable="column.sortable"
                        :align="column.align"
                        :header-align="column.align"
                        :fixed="column.fixed || null"
                        :formatter="column.formatter ||null">
                    </el-table-column>
                    <el-table-column align="center" width="80">
                        <template  #default="{ row }">
                            <div class="flex justify-around">
                                <el-button type="success" 
                                           circle
                                           @click="carga_DetCom(row)">
                                    <el-icon>
                                        <comentarioSnib />
                                    </el-icon>
                                </el-button>
                            </div>
                        </template>
                    </el-table-column>
                </el-table>
            </el-header>
        </el-container>
        <div>
            <el-container style="height: 300px; border: 1px solid #eee">
                <el-header>
                <el-table
                    :data="tableDetCom"
                    :highlight-current-row= true
                    :border="true"
                    height="400"
                    style="width: 100%">
                    <div v-if="tableDetCom.length==='0'">
                            No hay vista de los comentarios 
                    </div>
                
                    <el-table-column
                        v-for="(column) in tableColDet"
                        :key="column.label"
                        :label="column.label"
                        :prop="column.prop"
                        :column-key="column.prop"
                        :min-width="column.minWidth"
                        :sortable="column.sortable"
                        :align="column.align"
                        :header-align="column.align"
                        :fixed="column.fixed || null"
                        :formatter="column.formatter ||null">
                    </el-table-column>
                </el-table>
                </el-header>
            </el-container>
        </div>
    </div>
</template>
<script setup>

    import { ref, onMounted, watch } from 'vue';
    import { ChatDotSquare } from '@element-plus/icons-vue';
    import comentarioSnib from '@/Components/Biotica/Icons/Comentarios.vue';

    const props = defineProps({
        taxon: {
            type: Object
        },
    });

    const comSnib = ref([]);
    const tableColumns = ref([
        {
            prop: "idnombre",
            label: "IdNombre",
            minWidth: 80,
            sortable: true,
            hidden: false,
            align: 'left',
            fixed: true
        },
        {
            prop: "comentarioscat",
            label: "Comentarios SCAT",
            minWidth: 80,
            sortable: true,
            hidden: false,
            align: 'left',
            fixed: true
        },
        {
            prop: "Conteo",
            label: "Conteo",
            minWidth: 80,
            sortable: true,
            hidden: false,
            align: 'left',
            fixed: true
        }
    ]);

    const tableDetCom = ref([]);
    const tableColDet = ref([
        {
            prop: "comentarioscat",
            label: "Comenetarios SCAT",
            minWidth: 80,
            sortable: true,
            hidden: false,
            align: 'left',
            fixed:true
        },
        {
            prop: "llavenombre",
            label: "LLaveNombre",
            minWidth: 80,
            sortable: true,
            hidden: false,
            align: 'left',
            fixed:true
        }
    ]);

    watch(
        ()=> props.taxon,
        (newValue, oldValue) => {
            carga_AcumSnib();
            comSnib.value=[];
            tableDetCom.value=[];
            //console.log("Entre al watch");
        },
        //carga_inicio(),
        { deep: true, inmediate: true }
    );

    onMounted(() => {
        try {
            console.log("Este es el taxon Actual", props.taxon);
            carga_AcumSnib();
        } catch (error) {
            console.error('Error al obtener el usuario: ', error);
        }
    });

    const carga_AcumSnib = async () =>{
       
        const  params = {
            idNombre: props.taxon.id
        };

        const response = await axios.get('/carga-AcumuladoSnib', { params } );

        if (response.status === 200) {
            comSnib.value = response.data;
        }
        else {
            console.log("Se presentó un error en la recuperación de los datos");
        }
    };

    const carga_DetCom = async (row) => {

        const params = {
            idNombre: row.idnombre,
            comentarios: row.comentarioscat
        };

        const response = await axios.get('/carga-ComDet', { params } ); 

        if (response.status === 200) {
            tableDetCom.value = response.data;
        }
        else {
            console.log("Se presentó un error en la recuperación de los datos");
        }
    }
</script>