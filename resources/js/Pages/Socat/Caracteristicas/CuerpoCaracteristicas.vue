<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";
import NuevoButton from "@/Components/Biotica/NuevoButton.vue";
import EditarButton from "@/Components/Biotica/EditarButton.vue";
import EliminarButton from "@/Components/Biotica/EliminarButton.vue";
import GuardarButton from "@/Components/Biotica/GuardarButton.vue";
import DialogGeneral from "@/Components/Biotica/DialogGeneral.vue";
import NotificacionExitoErrorModal from "@/Components/Biotica/NotificacionExitoErrorModal.vue";
import BotonAceptar from "@/Components/Biotica/BotonAceptar.vue";
import BotonCancelar from "@/Components/Biotica/BotonCancelar.vue";
import { ref, computed, watch, onMounted, nextTick, h } from "vue";
import BotonSalir from '@/Components/Biotica/SalirButton.vue';

import {
  ElTree,
  ElMessage,
  ElMessageBox,
  ElInput,
  ElRadioGroup,
  ElRadio,
  ElForm,
  ElFormItem,
} from "element-plus";
import { router, usePage } from "@inertiajs/vue3";
import LayoutCuerpo from '@/Components/Biotica/LayoutCuerpo.vue';


const notificacionVisible = ref(false);
const notificacionTitulo = ref("");
const notificacionMensaje = ref("");
const notificacionTipo = ref("info");
const notificacionDuracion = ref(5000);
const treeRef = ref(null);
const expandedNodeIds = ref(new Set());
const nodeIdToFocus = ref(null);
const nodeIdToSelectAfterInsert = ref(null);
const nodeDataForDeleteConfirmation = ref(null);

const nodeIdToScrollToAfterNotification = ref(null);

const expandedKeysArray = computed(() => Array.from(expandedNodeIds.value));

const handleNodeExpand = (data, node) => {
  expandedNodeIds.value.add(data.IdCatNombre);
  handleNodeSelected(data, node);
};

const handleNodeCollapse = (data, node) => {
  expandedNodeIds.value.delete(data.IdCatNombre);
  handleNodeSelected(data, node);
};

const scrollToNode = (nodeId) => {
  nextTick(() => {
    const element = document.getElementById(`tree-node-${nodeId}`);
    if (element) {
      element.scrollIntoView({ behavior: "smooth", block: "center" });
    } else {
      console.warn(`scrollToNode: Elemento con ID="tree-node-${nodeId}" no encontrado para desplazar.`);
    }
  });
};

const expandAncestors = (nodeIdToExpand) => {
  const node = treeRef.value?.getNode(nodeIdToExpand);
  if (node && node.parent) {
    let parentNode = node.parent;
    while (parentNode && parentNode.level > 0) {
      parentNode.expand();
      parentNode = parentNode.parent;
    }
  }
};
const findNodeInTree = (nodes, nodeIdToFind) => {
  if (!nodes || !Array.isArray(nodes)) return null;
  const targetIdStr = String(nodeIdToFind);
  for (const node of nodes) {
    if (String(node.IdCatNombre) === targetIdStr) return node;
    if (node.children && node.children.length > 0) {
      const found = findNodeInTree(node.children, targetIdStr);
      if (found) return found;
    }
  }
  return null;
};
const props = defineProps({
  treeDataProp: { type: Array, required: true, default: () => [] },
  flatTreeDataProp: { type: Array, required: true, default: () => [] },
});
const mostrarNotificacion = (
  titulo,
  mensaje,
  tipo = "info",
  duracion = 5000
) => {
  notificacionTitulo.value = titulo;
  notificacionMensaje.value = mensaje;
  notificacionTipo.value = tipo;
  notificacionDuracion.value = duracion;
  notificacionVisible.value = true;
};

const cerrarNotificacion = () => {
  notificacionVisible.value = false;
  if (nodeIdToScrollToAfterNotification.value) {
    selectAndFocusNode(nodeIdToScrollToAfterNotification.value);
    nodeIdToScrollToAfterNotification.value = null;
  }
};

const localTreeData = ref([]);

const deepCopy = (data) => JSON.parse(JSON.stringify(data));
const sortNodesAlphabetically = (nodes) => {
  if (!nodes || !Array.isArray(nodes) || nodes.length === 0) return;
  nodes.sort((a, b) =>
    (a.Descripcion || "").localeCompare(b.Descripcion || "", undefined, {
      sensitivity: "base",
    })
  );
  nodes.forEach((node) => {
    if (node.children && node.children.length)
      sortNodesAlphabetically(node.children);
  });
};

const selectAndFocusNode = (nodeId, retries = 0) => {
  const MAX_RETRIES = 10;
  const RETRY_DELAY = 100;

  nextTick(() => {
    if (!treeRef.value) {
      return;
    }

    const node = treeRef.value.getNode(nodeId);

    if (node) {

      expandAncestors(nodeId);

      let parentNode = node.parent;
      while (parentNode && parentNode.level > 0) {
        expandedNodeIds.value.add(parentNode.data.IdCatNombre);
        parentNode = parentNode.parent;
      }

      treeRef.value.setCurrentKey(nodeId);
      selectedNode.value = node.data;
      setTimeout(() => {
        scrollToNode(nodeId);
      }, 150);

      nodeIdToSelectAfterInsert.value = null;
      nodeIdToFocus.value = null;

    } else if (retries < MAX_RETRIES) {
      setTimeout(() => selectAndFocusNode(nodeId, retries + 1), RETRY_DELAY);
    } else {
      console.error(`selectAndFocusNode: Fallo al encontrar el nodo con ID ${nodeId} después de ${MAX_RETRIES} intentos.`);
      nodeIdToSelectAfterInsert.value = null;
      nodeIdToFocus.value = null;
    }
  });
};

watch(
  () => props.treeDataProp,
  (newVal) => {
    const copiedData = deepCopy(newVal);
    sortNodesAlphabetically(copiedData);
    localTreeData.value = copiedData;

    let idToProcess = null;
    if (nodeIdToSelectAfterInsert.value) {
      idToProcess = String(nodeIdToSelectAfterInsert.value);
    } else if (nodeIdToFocus.value) {
      idToProcess = String(nodeIdToFocus.value);
    }

    if (idToProcess) {
      selectAndFocusNode(idToProcess);
    }
  },
  { immediate: true, deep: true }
);


onMounted(() => {
  if (localTreeData.value && localTreeData.value.length > 0) {
    const firstNodeId = localTreeData.value[0].IdCatNombre;
    selectAndFocusNode(firstNodeId);
  }
});

const selectedNode = ref(null);
const esModalVisible = ref(false);
const page = usePage();
const handleNodeSelected = (data, node) => {
  if (esModalVisible.value) {
    ElMessage.info("Hay una operación en curso. Por favor, guarde o cancele.");
    treeRef.value?.setCurrentKey(selectedNode.value?.IdCatNombre);
    return;
  }
  selectedNode.value = data;
};
const formModalRef = ref(null);
const descripcionInputRef = ref(null);
const formModal = ref({ Descripcion: "" });
const modalMode = ref("");
const opcionNivel = ref("mismo");
const nodoEnModal = ref(null);
const modalRules = {
  Descripcion: [
    {
      required: true,
      message:
        "La descripción es un dato obligatorio, no puede quedar en blanco.",
      trigger: "blur",
    },
    {
      min: 1,
      max: 255,
      message: "La longitud debe estar entre 1 y 255 caracteres.",
      trigger: "blur",
    },
  ],
};

const abrirModalParaInsertar = () => {
  if (!selectedNode.value && props.treeDataProp.length > 0) {
  }
  modalMode.value = "insertar";
  formModal.value = { Descripcion: "" };
  opcionNivel.value = selectedNode.value ? "mismo" : "raiz";
  esModalVisible.value = true;

  nextTick(() => {
    formModalRef.value?.clearValidate();
    setTimeout(() => {
      if (descripcionInputRef.value) {
        descripcionInputRef.value.focus();
      }
    }, 100);
  });
};

const abrirModalParaEditar = () => {
  if (!selectedNode.value) {
    ElMessage.warning("Por favor, seleccione un nodo para editar.");
    return;
  }
  modalMode.value = "editar";
  nodoEnModal.value = { ...selectedNode.value };
  formModal.value = { Descripcion: selectedNode.value.Descripcion };
  esModalVisible.value = true;
  nextTick(() => {
    formModalRef.value?.clearValidate();
    setTimeout(() => {
      if (descripcionInputRef.value) {
        descripcionInputRef.value.focus();
        const nativeInput = descripcionInputRef.value.$el.querySelector('input');
        if (nativeInput) {
          const len = nativeInput.value.length;
          nativeInput.setSelectionRange(len, len);
        }
      }
    }, 100);
  });
};

const modalTitle = computed(() => {
  return modalMode.value === "editar" ? "Modificar la característica seleccionada" : "Ingresar una nueva característica";
});

const guardarDesdeModal = async () => {
  if (!formModalRef.value) return;
  const isValid = await formModalRef.value.validate();
  if (!isValid) {
    return;
  }

  const proceedWithSave = () => {
    ElMessageBox.close();
    if (modalMode.value === "editar") {
      const datosUpdate = { Descripcion: formModal.value.Descripcion.trim() };
      const nodeId = nodoEnModal.value.IdCatNombre;
      router.put(`/caracteristicas-taxon/${nodeId}`, datosUpdate, {
        preserveState: true,
        preserveScroll: true,
        onSuccess: (page) => {
          cerrarModalOperacion();
          mostrarNotificacion(
            "Ingreso",
            "La información ha sido modificada correctamente.",
            "success"
          );
          nextTick(() => {
            const nodeToSelect = findNodeInTree(localTreeData.value, nodeId);
            if (nodeToSelect) {
              selectedNode.value = nodeToSelect;
              treeRef.value?.setCurrentKey(nodeId);
              expandAncestors(nodeId);
              scrollToNode(nodeId);
            }
          });
        },
        onError: (errors) => {
          const displayErrorMessage =
            errors.Descripcion?.[0] || "Ocurrió un error al actualizar.";
          mostrarNotificacion(
            "Error del Servidor",
            displayErrorMessage,
            "error"
          );
        },
      });
    } else if (modalMode.value === "insertar") {
      if (opcionNivel.value !== "raiz" && !selectedNode.value) {
        return mostrarNotificacion(
          "Error",
          "Se requiere un nodo de referencia.",
          "error"
        );
      }
      const calculoNiveles = calcularNivelesParaNuevoNodo(
        selectedNode.value,
        opcionNivel.value,
        props.flatTreeDataProp
      );
      if (!calculoNiveles) return;
      const datosInsert = {
        Descripcion: formModal.value.Descripcion.trim(),
        IdAscendente: calculoNiveles.idPadre,
        ...calculoNiveles.niveles,
      };
      router.post("/caracteristicas-taxon", datosInsert, {
        preserveState: true,
        preserveScroll: true,
        onSuccess: (page) => {
          cerrarModalOperacion();

          const finalNewNodeId = page.props.flash?.newNodeId;
          if (finalNewNodeId) {
            nodeIdToScrollToAfterNotification.value = finalNewNodeId;
          }
        },
        onError: (errors) => {
        },
      });
    }
  };

  if (modalMode.value === 'insertar') {
    proceedWithSave();
  } else {
    const nombreCaracteristica = formModal.value.Descripcion.trim();
    ElMessageBox({
      title: "Confirmar modificación",
      showConfirmButton: false,
      showCancelButton: false,
      customClass: "message-box-diseno-limpio",
      message: h("div", { class: "custom-message-content" }, [
        h("div", { class: "body-content" }, [
          h("div", { class: "custom-warning-icon-container" }, [
            h(
              "div",
              {
                class: "custom-warning-circle",
                style: "background-color: #e6a23c;",
              },
              "!"
            ),
          ]),
          h("div", { class: "text-container" }, [
            h(
              "p",
              null,
              `¿Estás seguro de que deseas guardar los cambios para "${nombreCaracteristica}"?`
            ),
          ]),
        ]),
        h("div", { class: "footer-buttons" }, [
          h(BotonCancelar, { onClick: () => ElMessageBox.close() }),
          h(BotonAceptar, { texto: "Sí, Guardar", onClick: proceedWithSave }),
        ]),
      ]),
    }).catch(() => {
    });
  }
};

const cerrarModalOperacion = () => {
  esModalVisible.value = false;
  nodoEnModal.value = null;
  formModal.value = { Descripcion: "" };
  modalMode.value = "";
  opcionNivel.value = "mismo";
  
  if (formModalRef.value) {
    formModalRef.value.clearValidate();
  }
};


const handleEliminar = () => {
  if (esModalVisible.value)
    return ElMessage.info("Cierre cualquier operación en curso.");
  if (!selectedNode.value)
    return ElMessage.warning("Por favor, seleccione un nodo para eliminar.");

  const tieneHijos =
    selectedNode.value.children && selectedNode.value.children.length > 0;
  if (tieneHijos)
    return mostrarNotificacion(
      "Acción no permitida",
      "No es posible eliminar el elemento seleccionado ya que tiene características subordinadas.",
      "error"
    );

  nodeDataForDeleteConfirmation.value = { ...selectedNode.value };

  const nombreCaracteristica = nodeDataForDeleteConfirmation.value.Descripcion;
  const mensaje = `¿Está seguro de eliminar la característica "${nombreCaracteristica}"? Esta acción no se puede revertir.`;

  ElMessageBox({
    title: "Confirmar eliminación",
    showConfirmButton: false,
    showCancelButton: false,
    customClass: "message-box-diseno-limpio",
    message: h("div", { class: "custom-message-content" }, [
      h("div", { class: "body-content" }, [
        h("div", { class: "custom-warning-icon-container" }, [
          h("div", { class: "custom-warning-circle" }, "!"),
        ]),
        h("div", { class: "text-container" }, [h("p", null, mensaje)]),
      ]),
      h("div", { class: "footer-buttons" }, [
        h(BotonCancelar, { onClick: () => ElMessageBox.close() }),
        h(BotonAceptar, {
          texto: "Sí, Eliminar",
          onClick: proceedWithDeletion,
        }),
      ]),
    ]),
  }).catch();
};

const proceedWithDeletion = async () => {
  ElMessageBox.close();
  if (!nodeDataForDeleteConfirmation.value) return;

  const { IdCatNombre, Descripcion, IdAscendente } =
    nodeDataForDeleteConfirmation.value;
  const targetUrl = `/caracteristicas-taxon/${IdCatNombre}`;
  const nombreCaracteristica = Descripcion || IdCatNombre;

  try {
    await axios.delete(targetUrl);

    router.reload({
      only: ["treeDataProp", "flatTreeDataProp"],
      preserveScroll: true,
      onSuccess: () => {
        mostrarNotificacion(
          "Eliminación exitosa",
          `La característica "${nombreCaracteristica}" ha sido eliminada correctamente.`,
          "success"
        );

        const parentNodeData = findNodeInTree(
          localTreeData.value,
          IdAscendente
        );
        if (parentNodeData) {
          selectedNode.value = parentNodeData;
          treeRef.value?.setCurrentKey(IdAscendente);
          scrollToNode(IdAscendente);
        } else {
          selectedNode.value = null;
        }
      },
    });
  } catch (error) {
    const errorMsg =
      error.response?.data?.error ||
      "Ocurrió un error al eliminar la característica.";
    mostrarNotificacion("Error al Eliminar", errorMsg, "error");
  } finally {
    nodeDataForDeleteConfirmation.value = null;
  }
};

const MAX_NIVELES = 7;
const calcularNivelesParaNuevoNodo = (
  nodoReferencia,
  opcion,
  todosLosNodos
) => {
  const nuevosNiveles = {};
  for (let i = 1; i <= MAX_NIVELES; i++) nuevosNiveles[`Nivel${i}`] = 0;
  let idPadreDelNuevoNodoActual = null;
  if (opcion === "raiz") {
    let maxNivel1Raiz = 0;
    todosLosNodos.forEach((nodo) => {
      const esRaizExistente =
        (nodo.IdAscendente === null ||
          String(nodo.IdAscendente) === String(nodo.IdCatNombre)) &&
        nodo.Nivel1 > 0 &&
        [2, 3, 4, 5, 6, 7].every((n) => (nodo[`Nivel${n}`] || 0) === 0);
      if (esRaizExistente) maxNivel1Raiz = Math.max(maxNivel1Raiz, nodo.Nivel1);
    });
    nuevosNiveles["Nivel1"] = maxNivel1Raiz + 1;
  } else if (opcion === "inferior") {
    idPadreDelNuevoNodoActual = nodoReferencia.IdCatNombre;
    let profundidadPadre = 0;
    for (let i = 1; i <= MAX_NIVELES; i++) {
      if (
        nodoReferencia[`Nivel${i}`] !== 0 &&
        nodoReferencia[`Nivel${i}`] != null
      ) {
        nuevosNiveles[`Nivel${i}`] = nodoReferencia[`Nivel${i}`];
        profundidadPadre = i;
      } else break;
    }
    const nivelParaSecuencia = profundidadPadre + 1;
    if (nivelParaSecuencia <= MAX_NIVELES) {
      const columnaNivelSecuencia = `Nivel${nivelParaSecuencia}`;
      let maxValorSecuencia = 0;
      todosLosNodos.forEach((nodo) => {
        if (String(nodo.IdAscendente) === String(idPadreDelNuevoNodoActual)) {
          let coincideHastaProfundidadPadre = true;
          for (let i = 1; i <= profundidadPadre; i++) {
            if (
              (nodo[`Nivel${i}`] || 0) !== (nuevosNiveles[`Nivel${i}`] || 0)
            ) {
              coincideHastaProfundidadPadre = false;
              break;
            }
          }
          if (coincideHastaProfundidadPadre)
            maxValorSecuencia = Math.max(
              maxValorSecuencia,
              nodo[columnaNivelSecuencia] || 0
            );
        }
      });
      nuevosNiveles[columnaNivelSecuencia] = maxValorSecuencia + 1;
    } else {
      mostrarNotificacion(
        "Error",
        "Profundidad máxima de niveles excedida para hijo.",
        "error"
      );
      return null;
    }
  } else {
    idPadreDelNuevoNodoActual = nodoReferencia.IdAscendente;
    let nivelDeSecuenciaDelNodoReferencia = 0;
    for (let i = 1; i <= MAX_NIVELES; i++) {
      if (
        nodoReferencia[`Nivel${i}`] !== 0 &&
        nodoReferencia[`Nivel${i}`] != null
      )
        nivelDeSecuenciaDelNodoReferencia = i;
      else break;
    }
    if (nivelDeSecuenciaDelNodoReferencia === 0) {
      mostrarNotificacion(
        "Error",
        "Error: Nodo de referencia sin niveles válidos para hermano.",
        "error"
      );
      return null;
    }
    const columnaDeSecuenciaHermanos = `Nivel${nivelDeSecuenciaDelNodoReferencia}`;
    if (idPadreDelNuevoNodoActual) {
      const nodoPadreComun = todosLosNodos.find(
        (n) => String(n.IdCatNombre) === String(idPadreDelNuevoNodoActual)
      );
      if (nodoPadreComun) {
        for (let i = 1; i < nivelDeSecuenciaDelNodoReferencia; i++)
          nuevosNiveles[`Nivel${i}`] = nodoPadreComun[`Nivel${i}`] || 0;
      }
    }
    let maxValorSecuencia = 0;
    todosLosNodos.forEach((nodo) => {
      if (String(nodo.IdAscendente) === String(idPadreDelNuevoNodoActual)) {
        let coincidenPrevios = true;
        for (let i = 1; i < nivelDeSecuenciaDelNodoReferencia; i++) {
          const valorNivelPrevioNodoIterado = nodo[`Nivel${i}`] || 0;
          const valorNivelPrevioHeredado = nuevosNiveles[`Nivel${i}`] || 0;
          if (valorNivelPrevioNodoIterado !== valorNivelPrevioHeredado) {
            coincidenPrevios = false;
            break;
          }
        }
        if (coincidenPrevios)
          maxValorSecuencia = Math.max(
            maxValorSecuencia,
            nodo[columnaDeSecuenciaHermanos] || 0
          );
      }
    });
    nuevosNiveles[columnaDeSecuenciaHermanos] = maxValorSecuencia + 1;
  }
  return { niveles: nuevosNiveles, idPadre: idPadreDelNuevoNodoActual };
};
const isAccionDependienteDeNodoDeshabilitada = computed(
  () => !selectedNode.value || esModalVisible.value
);
</script>

<template>
  <LayoutCuerpo :usar-app-layout="false" titulo-pag="Características Taxonómicas"
    titulo-area="Catálogo de características asociadas al taxón">

    <el-card class="box-card tree-card">
      <template #header>
        <div class="header-container">
          <div class="left-header-content"></div>
          <div class="right-header-content">
            <div class="action-group">
              <NuevoButton @crear="abrirModalParaInsertar" toolPosicion="bottom" :disabled="esModalVisible" />
              <EditarButton @editar="abrirModalParaEditar" toolPosicion="bottom"
                :disabled="isAccionDependienteDeNodoDeshabilitada" />
              <EliminarButton @eliminar="handleEliminar" toolPosicion="bottom"
                :disabled="isAccionDependienteDeNodoDeshabilitada" />
              <BotonSalir />
            </div>
          </div>
        </div>
      </template>

      <el-tree v-if="localTreeData && localTreeData.length" ref="treeRef" :data="localTreeData"
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
      </div>
    </el-card>

  </LayoutCuerpo>

  <Teleport to="body">
    <DialogGeneral v-model="esModalVisible" :bot-cerrar="true" :press-esc="true" @close="cerrarModalOperacion">
      <div class="dialog-header">
        <h3>{{ modalTitle }}</h3>
      </div>
      <div class="dialog-body-container">
        <div class="form-actions">
          <GuardarButton @click="guardarDesdeModal" />
          <BotonSalir accion="cerrar" @salir="cerrarModalOperacion" />
        </div>
        <el-form :model="formModal" ref="formModalRef" :rules="modalRules" label-position="top"
          @submit.prevent="guardarDesdeModal">

          <div v-if="modalMode === 'insertar' && selectedNode" class="mb-4">
            <label class="block text-sm font-medium text-gray-700 mb-1">Posición:</label>
            <el-radio-group v-model="opcionNivel">
              <el-radio value="mismo">Mismo nivel</el-radio>
              <el-radio value="inferior">Nivel inferior</el-radio>
            </el-radio-group>
          </div>

          <el-form-item prop="Descripcion">
            <template #label>
              {{ modalMode === "editar" ? "Nueva descripción:" : "Descripción de la característica:" }}
            </template>
            <el-input ref="descripcionInputRef" id="descripcionModalInput" v-model="formModal.Descripcion" placeholder="Ingrese la descripción"
              clearable maxlength="255" show-word-limit />
          </el-form-item>

        </el-form>

      </div>
    </DialogGeneral>

    <NotificacionExitoErrorModal :visible="notificacionVisible" :titulo="notificacionTitulo"
      :mensaje="notificacionMensaje" :tipo="notificacionTipo" :duracion="notificacionDuracion"
      @close="cerrarNotificacion" />
  </Teleport>
</template>

<style>
.message-box-diseno-limpio .el-message-box__header {
  border-bottom: none;
}

.message-box-diseno-limpio .el-message-box__content {
  padding: 10px 20px 20px 20px;
}

.custom-message-content {
  display: flex;
  flex-direction: column;
}

.body-content {
  display: flex;
  align-items: center;
  gap: 15px;
}

.text-container p {
  margin: 0;
  line-height: 1.5;
  color: #606266;
}

.custom-warning-icon-container {
  flex-shrink: 0;
}

.custom-warning-circle {
  width: 30px;
  height: 30px;
  border-radius: 90%;
  background-color: #f56c6c;
  color: white;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 28px;
  font-weight: bold;
  line-height: 1;
}

.footer-buttons {
  display: flex;
  justify-content: flex-end;
  gap: 10px;
  margin-top: 25px;
}

.custom-element-tree {
  font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
  font-size: 14px;
  line-height: 20px;
}

.custom-element-tree .el-tree-node__content {
  padding: 0px 4px;
  border-bottom: none;
  height: auto;
  margin-bottom: 0;
  border-radius: 4px;
}

.custom-element-tree .el-tree-node__content:hover {
  background-color: #f4f6f8;
}

.custom-element-tree .el-tree-node.is-current>.el-tree-node__content {
  background-color: #e4f5e1;
  font-weight: 500;
  color: #007bff;
}

.custom-element-tree .el-tree-node__expand-icon {
  font-size: 1.2em;
  color: #909399;
}

.custom-element-tree .el-tree-node__expand-icon:hover {
  color: #606266;
}

.tree-card>.el-card__body {
  overflow-y: auto;
  flex-grow: 1;
  padding: 10px;
  border: 1px solid #ebeef5;
  border-radius: 4px;
  margin: 0 24px 24px 24px;
}
</style>

<style scoped>
.tree-card {
  width: 100%;
  max-width: 1600px;
  max-height: 726px;
  display: flex;
  flex-direction: column;

}

:deep(.el-card__header) {
  padding: 16px 24px !important;
  border-bottom: 1px solid #e4e7ed !important;
  flex-shrink: 0;
}

:deep(.el-card__body) {
  padding: 0;
  flex-grow: 1;
  display: flex;
  flex-direction: column;
  min-height: 0;
}

.header-container {
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.action-group {
  display: flex;
  gap: 10px;
}

.no-data-message {
  text-align: center;
  color: #777;
  padding: 20px;
  font-style: italic;
  margin: auto;
}

.mb-4 {
  margin-bottom: 1rem;
}

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

.dialog-header h3 {
  margin: 0;
  font-size: 1.25rem;
  font-weight: 600;
  color: #303133;
}

.dialog-body-container {
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

.form-actions {
  display: flex;
  justify-content: flex-end;
  gap: 10px;
}

:deep(.el-form-item) {
  margin-bottom: 22px;
}

:deep(.el-form-item__label) {
  padding-bottom: 4px !important;
  line-height: normal !important;
  font-size: 0.9em;
  color: #606266;
}
</style>