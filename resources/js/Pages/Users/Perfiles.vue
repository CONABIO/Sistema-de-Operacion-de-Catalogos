<template>
    <LayoutCuerpo :usar-app-layout="true" titulo-pag="Usuarios"
    titulo-area="Catálogo de usuarios">

    <el-card class="box-card tree-card" shadow="never">
        <TablaFiltrable
        :columnas = "columnasDefinidas"
        :datos = "tablaUsuariosConRol"
        :total-items="totalRegUsr"
        :alturaTabla = 320
        :mostrarAcci = "true"
        :moduloSocat = "'MnuUsuarios'"
        :mostrarGuardar = "true"
        :valoresOpcion = "opcionesRoles"
        :highlight-current-row = "true"
        @eliminar-item = "manejarEliminarItem"
        @guardar="guardarUsuario"
        @lista-Actual = "recibePerfil"
        @row-click = "manejaClick">

        </TablaFiltrable>
        
        <!--TablaFiltrable 
                          :columnas="columnasDefinidas"  
                          :container-class="'main-section'"                           
                          :datos = "tablaNomenclatura" 
                          :total-items="totalRegNom"
                          :opciones-filtro = "opcionesFiltroNomenclatura"
                          :highlight-current-row = "true"
                          :origen = "true"
                          :itemsPerPage = 2
                          :mostrarBiblio = "true"
                          :mostrarAcci = "true"
                          :moduloSocat="moduloSocat"
                          :mostrarSalir = "false" 
                          :mostrarNuevo = "false"
                          :alturaTabla = 220
                          @eliminar-item = "manejarEliminarItem"
                          @editar-item = "manejarEditar"
                          @row-click = "manejaClick"
                          @abrir-Biblio = "abrirBiblio">
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
                        </TablaFiltrable-->

      <!--el-tree v-if="localTreeData && localTreeData.length" ref="treeRef" :data="localTreeData"
        :props="{ children: 'children', label: 'Descripcion' }" node-key="IdCatNombre"
        :current-node-key="selectedNode?.IdCatNombre" :highlight-current="true" :expand-on-click-node="true"
        :default-expanded-keys="expandedKeysArray" @node-expand="handleNodeExpand" @node-collapse="handleNodeCollapse"
        @node-click="handleNodeSelected" class="custom-element-tree">
        <template #default="{ node, data }">
          <span :id="`tree-node-${data.IdCatNombre}`" class="custom-tree-node-content">
            <span>{{ node.label }}</span>
          </span>
        </template>
      </el-tree>
      <div v-else class="no-data-message">
        No hay datos de características para mostrar.
      </div-->
    </el-card>
    <Teleport to="body">
        <NotificacionExitoErrorModal :visible="notificacionVisible" :titulo="notificacionTitulo"
                :mensaje="notificacionMensaje" :tipo="notificacionTipo" :duracion="notificacionDuracion"
                @close="cerrarNotificacion" />
    </Teleport>
  </LayoutCuerpo>
</template>

<script setup>
    import LayoutCuerpo from '@/Components/Biotica/LayoutCuerpo.vue';
    import TablaFiltrable from "@/Components/Biotica/TablaFiltrable.vue";
    import { ref, onMounted, computed, h } from 'vue';
    import usePermisos from '@/composables/usePermisos';
    import { Management } from '@element-plus/icons-vue';
    import { ElMessageBox } from 'element-plus';
    import NotificacionExitoErrorModal from "@/Components/Biotica/NotificacionExitoErrorModal.vue";
    import BotonAceptar from '@/Components/Biotica/BotonAceptar.vue';
    import BotonCancelar from '@/Components/Biotica/BotonCancelar.vue';

    const { permisos } = usePermisos();

    const tablaUsuarios = ref([]);
    const totalRegUsr = ref(0); 
    const listaRoles = ref([]);
    const rolActualizado = ref(0);
    const registroActual = ref([]);

    const notificacionVisible = ref(false);
    const notificacionTitulo = ref("");
    const notificacionMensaje = ref("");
    const notificacionTipo = ref("info");
    const notificacionDuracion = ref(5000);


    const columnasDefinidas = ref([
        {
            prop: 'Alias', label: 'Usuario', minWidth: '90', sortable: true,
            align: 'left', tipo: 'Texto', filtrable: true
        },
        {
            prop: 'name', label: 'Nombre Completo', minWidth: '160', sortable: true,
            align: 'left', tipo: 'Texto', filtrable: true
        },
        {
            prop: 'email', label:'Correo', minWidth: '160', sortable: false, align: 'center',
            tipo: 'Texto', filtrable: true
        },
        {
            prop: 'rol', label:'Perfil usuario', minWidth: '90', sortable: false, align: 'center',
            tipo: 'lista', filtrable: false
        }
    
    ]);

    const recibePerfil = (valor)=>{
        rolActualizado.value = valor;
    } 

    const manejaClick = (row) => {
        console.log("Esto vale el row: ", row);
        registroActual.value = row;
    }

    const opcionesRoles = computed(() => {
        return listaRoles.value.map(rol => ({
            id: rol.IdRol,
            descripcion: rol.Perfil
        }));
    });

    const tablaUsuariosConRol = computed(() => {
        return tablaUsuarios.value.map(usuario => ({
            ...usuario,
            rol: {
                id: usuario.IdRol,
                descripcion: opcionesRoles.value.find(
                    rol => rol.id === usuario.IdRol
                )?.descripcion ?? ''
            }
        }));
    });
    
    const manejarEliminarItem = (item) => {
        console.log("Este es el item: ", item);

        const procederConEliminacion = async () => {

        try {
            ElMessageBox.close();

            const response = await axios.delete('/elimina-usuario', { data: {id: item.id}});

            tablaUsuarios.value = response.data;

            mostrarNotificacion('Eliminación exitosa', `El usuario: ${item.Alias} fue eliminado correctamente.`, 'success');
        } catch (apiError) {
            mostrarNotificacionError('Aviso', `El usuario: ${item.Alias} no se pudo eliminar.`, 'success');
        }
        };
        const cancelarEliminacion = () => {
        ElMessageBox.close();
        };

        const mensaje = `Se eliminara el usuario: ${item.Alias}, perteneciente a ${item.name}. ¿Realmente desea realizarlo?. Esta acción no se puede revertir`;

        ElMessageBox({
        title: 'Confirmar eliminación', showConfirmButton: false, showCancelButton: false, customClass: 'message-box-diseno-limpio',
        message: h('div', { class: 'custom-message-content' }, [
            h('div', { class: 'body-content' }, [
            h('div', { class: 'custom-warning-icon-container' }, [h('div', { class: 'custom-warning-circle' }, '!')]),
            h('div', { class: 'text-container' }, [h('p', null, mensaje)])
            ]),
            h('div', { class: 'footer-buttons' }, [
            h(BotonCancelar, { onClick: cancelarEliminacion }),
            h(BotonAceptar, { onClick: procederConEliminacion }),
            ])
        ])
        }).catch(() => { });
    };

    const guardarUsuario = (item) =>{
        if(registroActual.IdRol === rolActualizado.value || rolActualizado.value === 0){
            console.log("No cambio el rol: ", rolActualizado.value);
            return;
        }else{
            const response = axios.post('/actualizaUsr', { data: {usr: registroActual.value, 
                                                                  perfil: rolActualizado.value}} ) ;            
            console.log("Si cambio el rol: ", rolActualizado.value);
        }
        console.log(item);
        rolActualizado.value = 0;
    }

    const cerrarNotificacion = () => {
        notificacionVisible.value = false;
    };

    const mostrarNotificacionError = (titulo, mensaje, tipo = "info", duracion = 5000) => {
        notificacionTitulo.value = titulo;
        notificacionMensaje.value = mensaje;
        notificacionTipo.value = tipo;
        notificacionDuracion.value = 0;
        notificacionVisible.value = true;
    };

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

    onMounted( async () => {
        const response = await axios.get('/cargar-usuarios');
        const roles = await axios.get('/carga-perfiles');
        
        if(response.status === 200 && roles.status === 200){
            tablaUsuarios.value = response.data;
            totalRegUsr.value = tablaUsuarios.value.length;
            listaRoles.value = roles.data;
        }
  });

</script>

