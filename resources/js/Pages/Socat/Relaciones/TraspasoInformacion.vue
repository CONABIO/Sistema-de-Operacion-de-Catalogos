<template>
    <div>
        <el-card class="box-card">
            <div class="common-layout">
                <el-container style="height: 98vh;">
                    <el-header class="header">
                        <div class="header-content">
                        <h2 class="titulo">Citas bibliograficas asociadas</h2>
                        </div>
                    </el-header>  
                        
                    <el-main style="padding: 15px; background: #fff; overflow: hidden;">    
                        <el-row justify="space-between" align="middle">

                            <span style="font-size: 18px; color: #8A2815; font-weight: bold;">
                                {{ props.taxonAct.label }} 
                            </span>

                            <div style="display: flex; gap: 50px;">
                                <BotonSalir accion="cerrar" @salir="cerrarDialogo"
                                                style="flex-shrink: 0; min-width: max-content;"/>
                            </div>
                        </el-row>
                        <br/>
                        <el-row>
                            <el-tabs type="card" v-model="tabInicial">
                                <el-tab-pane label="Nombre(s) común(es)" name="NomComun" v-if="nombresComunes.length > 0">
                                    <el-transfer v-model="value" :data="data" />
                                </el-tab-pane>
                                <el-tab-pane label="Caracteristicas" name="Caracteristicas" v-if="caracteristicas.length > 0">
                                    <el-transfer v-model="value" :data="data" />
                                </el-tab-pane>
                                <el-tab-pane label="Regiones" name="Regiones" v-if="regiones.length > 0">
                                    <el-transfer v-model="value" :data="data" />
                                </el-tab-pane>
                            </el-tabs>
                        </el-row>
                    </el-main>
                </el-container>
            </div>
        </el-card>
    </div>
</template>

<script setup>
    import { onMounted, ref } from 'vue';
    import BotonSalir from '@/Components/Biotica/SalirButton.vue';

    const taxValido = ref({});
    const taxSinonimo = ref({});

    const nombresComunes = ref({});
    const caracteristicas = ref({});
    const regiones= ref({});

    const emit = defineEmits(['cerrar']);
    const props = defineProps({
        taxonAct: {
            type: Object
        },
        sinonimo: {
            type: Object
        }    
    });

    const cerrarDialogo = () => {
        emit('cerrar');
    };

    onMounted( async () => {
        console.log("Este es el taxon valido que esta pasando: ", props.taxonAct.estatus);
        console.log("Este es el id del taxon valido que esta pasando: ", props.taxonAct.id);
        console.log("Este es el taxon sinonimo que esta pasando: ", props.sinonimo.Nombrecompleto.estatus);
        console.log("Este es el id del taxon sinonimo que esta pasando: ", props.sinonimo.idNombre);
        let resp;
        
        //resp = await axios.get(`/carga-taxon/${props.taxonAct.id}`);
       
        if(props.taxonAct.estatus === "Sinonimo"){
            console.log("Entre al primer if");
            resp = await axios.get(`/cargar-nomcomun-taxon/${props.taxonAct.id}`);
            nombresComunes.value = resp.data;
            console.log("Nombres Comunes: ", nombresComunes.value.length);
            resp = await axios.get(`/cargaCaracTaxon/${props.taxonAct.id}`);
            caracteristicas.value = resp.data;
            console.log("Caracteristicas: ", caracteristicas.value.length);
            resp = await axios.get(`/cargaRegionesTaxon/${props.taxonAct.id}`);
            regiones.value = resp.data.regPorNombre;
            console.log("Regiones: ", regiones.value.length);
            //nombreComun.value = resp
        }else if(props.sinonimo.Nombrecompleto.estatus === "Sinonimo") {
            console.log("Entre al segundo if", props.sinonimo.idNombre);
            resp = await axios.get(`/cargar-nomcomun-taxon/${props.sinonimo.idNombre}`);
            nombresComunes.value = resp.data;
            console.log("Nombres Comunes: ", nombresComunes.value.length);
            resp = await axios.get(`/cargaCaracTaxon/${props.sinonimo.idNombre}`);
            caracteristicas.value = resp.data;
            console.log("Caracteristicas: ", caracteristicas.value.length);
            resp = await axios.get(`/cargaRegionesTaxon/${props.sinonimo.idNombre}`);
            regiones.value = resp.data.regPorNombre;
            console.log("Regiones: ", regiones.value.length);
            //nombreComun.value = resp
        }

           
        /*

        resp = await axios.get(`/cargar-nomcomun-taxon/${props.taxonAct.id}`);

        console.log("Estos son los nombres comunes asocidos al taxón: actual: ", resp);

        resp = await axios.get(`/cargaCaracTaxon/${props.taxonAct.id}`);

        console.log("Estas son las caracteristicas asocidas al taxón actual: ", resp);

        resp = await axios.get(`/cargaRegionesTaxon/${props.taxonAct.id}`);

        console.log("Estas son las regiones asociadas al taxón actual: ", resp);

*/
        //respTaxAct = axios.get('/datosTraspaso', { data: { idNombre: props.taxonAct }});
        //respTaxRel = axios.get('/datosTraspaso', { data: { idNombre: props.sinonimo }});

       /* axios.delete('/elimina-RelBiblioNombre', { data: {idBiblio: item.IdBibliografia,
                                                                                  taxAct: taxonAct.value.id}});*/

        //if(props.taxonAct)
        /*const response = await axios.get('/cargar-tipoRel');
        const resTiposRel = await axios.get('/tipos-relacion/cargaInicial');

        if(resTiposRel.status === 200){
        tipRelacion.value = resTiposRel.data;
        }

        habCambioSinBas.value = true;

        if (response.status === 200) {            
            tiposRel.value = response.data;
            tiposRel.value.unshift({
                label: "Todos",
                value: 0
            });
        }
        await cargaGrupos();

        tipRel.value = [0];
        cargaRelaciones([0]);
        gruposTax.value = props.gruposTax;
        taxonAct.value = props.taxonAct;*/
    });

</script>

<style scoped>
    .box-card {
        width: 100%;
        max-width: 100%;
        margin: 0 auto;
        box-shadow: 0 2px 12px rgba(0, 0, 0, 0.1);
        height: 791px;
    }

    .header {
        background-color: #d9e1eb;
        padding: 15px;
        border-bottom: 1px solid #e0e0e0;
        height: auto !important;
        min-height: auto !important;
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

</style>