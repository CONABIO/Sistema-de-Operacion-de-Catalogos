    <template>
        <div>
            <el-card class="box-card" style="width: 1540px; margin: 20px auto; border-radius: 12px;">
                <div class="common-layout">
                    <el-container style="height: auto;">
                        <el-header class="header">
                            <div class="header-content">
                                <h1 class="titulo">Asociación de catalogos</h1>
                            </div>
                        </el-header>
                        <el-main style="padding: 10px; background: #fff; overflow: hidden;">
                            <el-row :gutter="21">
                                <el-col :span="18">
                                    <span style="font-size: 18px; color: #8A2815; font-weight: bold;">
                                        {{ props.taxonAct.label }}
                                    </span>
                                </el-col>
                                <el-col :span="5">
                                    <div style="display: flex; gap: 5px; ">
                                        <BotonCaract @click="abrirCaract"
                                            style="flex-shrink: 0; min-width: max-content;" />

                                        <BotonRegiones @click="abrirReg"
                                            style="flex-shrink: 0; min-width: max-content;" />

                                        <BotonNomComun @click="abrirNomCom"
                                            style="flex-shrink: 0; min-width: max-content;" />

                                        <BotonSalir accion="cerrar" @salir="closeDialog"
                                            style="flex-shrink: 0; min-width: max-content;" />
                                    </div>
                                </el-col>
                            </el-row>
                            <el-tabs type="card" v-model="tabInicial">
                                <el-tab-pane label="Nombre(s) común(es)" name="NomComun">
                                    <div
                                        style="height: 560px; border: 1px solid #ddd; border-radius: 8px; overflow: hidden; box-shadow: var(--el-border-color-light) 0px 0px 10px">
                                        <el-splitter lazy>
                                            <el-splitter-panel :size="'30%'">
                                                <div class="table-wrapper" style="height: 100%; padding: 5px;">
                                                    <TablaFiltrable ref="tablaNomComunPrincipalRef"
                                                        v-model:datos="tablaNomComun" v-model:totalItems="contRegNomCom"
                                                        endpoint="/busca-nombre-comun"
                                                        :columnas="columnasDefinidasNomCom"
                                                        :opciones-filtro="opcionesFiltroNomComun" :itemsPerPage="100"
                                                        :mostrarAcci="false" :alturaTabla="452"
                                                        :permitirSinSeleccion="true" :highlight-current-row="true"
                                                        :mostrarBiblio="false" :mostrarNuevo="false"
                                                        :mostrarEditar="false" :mostrarBorrar="false"
                                                        :mostrarSalir="false" :mostrarNomComun="true"
                                                        @row-click="clickNomComun" @abrirNomComun="abrirNomCom">
                                                    </TablaFiltrable>
                                                </div>
                                            </el-splitter-panel>

                                            <el-splitter-panel :size="'30%'">
                                                <div style="height: 100%;">
                                                    <el-splitter layout="vertical" style="height:100%;">
                                                        <el-splitter-panel>
                                                            <el-card class="panel-card list-panel" shadow="never">
                                                                <template #header>
                                                                    <div
                                                                        style="display: flex; align-items: center; margin-top: -4px; margin-bottom: -6px;">
                                                                        <span>Tipo de región</span>
                                                                        <el-button
                                                                            style="background-color: springgreen; margin-left:auto;"
                                                                            circle @click="handleManageTiposRegion">
                                                                            <IconoMundo />
                                                                        </el-button>
                                                                    </div>
                                                                </template>
                                                                <div class="demo-tree panel-nombre">
                                                                    <el-tree v-if="tiposRegionTreeData.length"
                                                                        ref="tiposRegionTreeRef" :key="treeKey"
                                                                        :data="tiposRegionTreeData"
                                                                        :props="{ children: 'children', label: 'Descripcion' }"
                                                                        node-key="IdTipoRegion"
                                                                        :highlight-current="true"
                                                                        :expand-on-click-node="true"
                                                                        :default-expanded-keys="expandedTipoRegionKeys"
                                                                        @node-click="handleTipoRegionSelected">
                                                                        <template #default="{ data }">
                                                                            <span
                                                                                :class="['nodo-tipo-region', { 'is-active-path': activePathIds.includes(data.IdTipoRegion) }]">
                                                                                {{ data.Descripcion }}
                                                                            </span>
                                                                        </template>
                                                                    </el-tree>
                                                                </div>
                                                            </el-card>
                                                        </el-splitter-panel>
                                                        <el-splitter-panel>
                                                            <el-card class="panel-card list-panel" shadow="never">
                                                                <template #header>
                                                                    <div style="display: flex; align-items: center; margin-top: -5px; margin-bottom: -6px; width: 100%; gap: 10px;">
                                                                        <span class="details-header-title">Región</span>

                                                                        <!-- BUSCADOR INSERTADO -->
                                                                        <div class="header-buscador" style="flex-grow: 1; max-width: 300px; margin: 0 10px;">
                                                                            <el-input
                                                                                v-model="filterText"
                                                                                placeholder="Escriba para buscar"
                                                                                clearable
                                                                                :disabled="buscadorDeshabilitado"
                                                                                @keyup.enter="irAlNodoBuscado"
                                                                            />
                                                                        </div>

                                                                        <el-button style="margin-left: auto;" circle>
                                                                            <BotonRegiones @click="abrirReg" />
                                                                        </el-button>
                                                                    </div>
                                                                </template>
                                                                <div class="demo-tree panel-nombre">
                                                                    <el-tree v-show="filteredRegionsTree.length"
                                                                        ref="treeRef" :key="treeKey"
                                                                        :data="filteredRegionsTree"
                                                                        :props="{ children: 'children', label: 'NombreRegion' }"
                                                                        node-key="IdRegion" :highlight-current="true"
                                                                        :expand-on-click-node="true"
                                                                        @node-click="handleNodeSelected">
                                                                        <template #default="{ node, data }">
                                                                            <span :id="'region-node-' + data.IdRegion"
                                                                                class="nodo-texto">
                                                                                {{ node.label }}
                                                                            </span>
                                                                        </template>
                                                                    </el-tree>
                                                                </div>
                                                            </el-card>
                                                        </el-splitter-panel>
                                                    </el-splitter>
                                                </div>
                                            </el-splitter-panel>

                                            <el-splitter-panel :size="'40%'">
                                                <div
                                                    style="height: 100%; border-left: 1px solid #ddd; display: flex; flex-direction: column; background: #fff;">
                                                    <div
                                                        style="padding: 10px; background: #f5f7fa; border-bottom: 1px solid #ddd; display: flex; justify-content: space-between; align-items: center;">
                                                        <span>Nombres asociados ({{ nombresAsociadosTaxon.length
                                                            }})</span>
                                                        <div style="display: flex; gap: 8px; align-items: center;">
                                                            <EditarButton @editar="activarEdicionGeneral" />
                                                            <EliminarButton @eliminar="confirmarEliminarAsociacion" />
                                                            <GuardarButton @confirmar="guardarCambiosObsGeneral"
                                                                :disabled="!editandoObsGeneral"
                                                                style="margin-right: 12px; margin-left: 12px;" />
                                                            <BotonTraspaso @traspasa="onCreaRelacion" />
                                                            <el-tooltip class="item" effect="dark"
                                                                content="Bibliografia" placement="top">
                                                                <el-button @click="abrirResumenRegiones" circle
                                                                    style="flex-shrink: 0; background-color: #509165; color: white;">
                                                                    <el-icon>
                                                                        <Management />
                                                                    </el-icon>
                                                                </el-button>
                                                            </el-tooltip>
                                                        </div>
                                                    </div>

                                                    <div style="flex: 1; overflow: auto; padding: 10px;">
                                                        <el-tree :data="nombresAsociadosTaxon"
                                                            :props="{ children: 'Regiones' }" node-key="id"
                                                            highlight-current>
                                                            <template #default="{ node, data }">
                                                                <span
                                                                    style="display: flex; align-items: center; gap: 8px; width: 100%;"
                                                                    @click="clickNomCom(data)">
                                                                    <template v-if="data.NombreComun">
                                                                        <span
                                                                            style="font-weight: bold; font-size: 13px; color: #333;">
                                                                            {{ data.NombreComun }}
                                                                        </span>
                                                                    </template>

                                                                    <template v-else>
                                                                        <Logo :rutaCategoria="data.Biblio?.url"
                                                                            style="width: 18px; height: 18px; flex-shrink: 0;" />
                                                                        <span style="font-size: 12px; color: #606266;">
                                                                            {{ data.Region }}
                                                                        </span>
                                                                    </template>
                                                                </span>
                                                            </template>
                                                        </el-tree>
                                                        <el-empty v-if="nombresAsociadosTaxon.length === 0"
                                                            description="No hay nombres asociados" :image-size="60" />
                                                    </div>

                                                    <div
                                                        style="padding: 12px; background: #fafafa; border-top: 1px solid #eee;">
                                                        <div
                                                            style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 5px;">
                                                            <span style="font-weight: bold; color: #606266;">{{
                                                                etiquetaObservaciones }}</span>
                                                        </div>
                                                        <el-input v-model="observacionesGeneral" type="textarea"
                                                            :rows="2" :placeholder="placeholderObservaciones"
                                                            style="font-size: 12px;" :disabled="!editandoObsGeneral" />
                                                    </div>
                                                </div>
                                            </el-splitter-panel>
                                        </el-splitter>
                                    </div>
                                </el-tab-pane>

                                <el-tab-pane label="Características" name="Caracteristicas">
                                    <el-container>
                                        <el-aside width="710px">
                                            <div
                                                style="height: 680px; border: 1px solid #ddd; border-radius: 4px; overflow: hidden; background: #fff;">
                                                <el-splitter layout="vertical">
                                                    <el-splitter-panel :min="50" :size="'79%'">
                                                        <div class="table-wrapper"
                                                            style="height: 100%; display: flex; flex-direction: column;">

                                                            <div style="flex: 1; padding: 10px; overflow: auto;">
                                                                <TablaFiltrable :columnas="columnasDefinidasCaract"
                                                                    :datos="tablaCaracteristicas"
                                                                    :opciones-filtro="opcionesFiltroCaract"
                                                                    :totalItems="totalRegCaract" :itemsPerPage="9"
                                                                    :mostrarBiblio="true" :mostrarAcci="false"
                                                                    :alturaTabla="280" :highlight-current-row="true"
                                                                    :mostrarNuevo="true" :mostrarEditar="true"
                                                                    :mostrarBorrar="true" :mostrarSalir="false"
                                                                    :mostrarGuardar="true" @row-click="clickCaract"
                                                                    @abrir-Biblio="abrirResumenCaractSolo"
                                                                    @nuevo-item="nuevoRelCaract"
                                                                    @editar-item="editarCarct"
                                                                    @eliminar-item="eliminarCaract"
                                                                    @guardar="guardarCaract" />
                                                            </div>
                                                        </div>
                                                    </el-splitter-panel>

                                                    <el-splitter-panel :size="'30%'">
                                                        <div
                                                            style="padding: 15px; background: #fff; border-top: 2px solid #eee; height: 100%;">
                                                            <p
                                                                style="font-size: 13px; color: #333; margin-bottom: 8px; font-weight: bold;">
                                                                Observaciones taxon - característica</p>
                                                            <div
                                                                style="display: flex; align-items: flex-start; gap: 10px;">
                                                                <el-input v-model="observacionesCaractGral"
                                                                    type="textarea" :disabled="actObsCaract" :rows="3"
                                                                    placeholder="Observaciones de la característica..."
                                                                    style="flex: 1;" />
                                                            </div>
                                                        </div>
                                                    </el-splitter-panel>
                                                </el-splitter>
                                            </div>
                                        </el-aside>

                                        <el-aside width="30px" />

                                        <el-aside width="710px">
                                            <div
                                                style="height: 630px; border: 1px solid #ddd; border-radius: 4px; overflow: hidden; background: #fff;">
                                                <el-splitter layout="vertical">
                                                    <el-splitter-panel :min="50" :size="'79%'">
                                                        <div class="table-wrapper"
                                                            style="height: 100%; display: flex; flex-direction: column;">
                                                            <div style="flex: 1; padding: 10px; overflow: auto;">
                                                                <TablaFiltrable :columnas="columnasDefinidasRegCaract"
                                                                    :datos="tablaCaractReg"
                                                                    :opciones-filtro="opcionesFiltroRegCaract"
                                                                    :totalItems="totalRegionCaract"
                                                                    :valoresOpcion="tiposDistribucion"
                                                                    :habOpciones="habOpciones" :itemsPerPage="9"
                                                                    :mostrarBiblio="true" :mostrarAcci="false"
                                                                    :alturaTabla="280" :highlight-current-row="true"
                                                                    :mostrarNuevo="false" :mostrarEditar="true"
                                                                    :mostrarBorrar="true" :mostrarSalir="false"
                                                                    :mostrarGuardar="true" @row-click="clickRegCaract"
                                                                    @abrir-Biblio="abrirResumenCaract"
                                                                    @guardar="guardarCaractReg"
                                                                    @editar-item="editarCaractReg"
                                                                    @eliminar-item="eliminarCaractReg"
                                                                    @lista-Actual="actValorLista" />
                                                            </div>
                                                        </div>
                                                    </el-splitter-panel>

                                                    <el-splitter-panel :size="'30%'">
                                                        <div
                                                            style="padding: 15px; background: #fff; border-top: 2px solid #eee; height: 100%;">
                                                            <p
                                                                style="font-size: 13px; color: #333; margin-bottom: 8px; font-weight: bold;">
                                                                Observaciones de características - region</p>
                                                            <div
                                                                style="display: flex; align-items: flex-start; gap: 10px;">
                                                                <el-input v-model="obsCaractReg" type="textarea"
                                                                    :rows="3" :disabled="actObsCaractReg"
                                                                    placeholder="Observaciones de la región..."
                                                                    style="flex: 1;" />
                                                            </div>
                                                        </div>
                                                    </el-splitter-panel>
                                                </el-splitter>
                                            </div>
                                        </el-aside>
                                    </el-container>
                                </el-tab-pane>
                                <el-tab-pane label="Regiones2" name="Region3">
                                    <div style="height: 500px; box-shadow: var(--el-border-color-light) 0px 0px 10px">
                                        <el-splitter lazy>
                                            <el-splitter-panel min="50">
                                                <div class="demo-panel panel-nombre">
                                                    <el-container style="width:100%; height:100%;">
                                                        <el-header height="40px"
                                                            style=" display:flex; justify-content:center; align-items:center;">
                                                            <span
                                                                style="font-size: 18px; color: #8A2815; font-weight: bold;">
                                                                Regiones asociadas al Taxón
                                                            </span>
                                                        </el-header>

                                                        <TablaFiltrable :columnas="colDefRegionNombre"
                                                            :datos="regionesNombre"
                                                            :opciones-filtro="opcionesFiltroRegCaract"
                                                            :totalItems="totalRegionesNom"
                                                            :valoresOpcion="tiposDistribucion"
                                                            :habOpciones="habOpciones" :itemsPerPage=4
                                                            :mostrarBiblio="true" :mostrarAcci="false" :alturaTabla=264
                                                            :highlight-current-row="true" :mostrarNuevo="false"
                                                            :mostrarRegion="true" :mostrarGuardar="true"
                                                            :mostrarEditar="true" :mostrarBorrar="true"
                                                            :mostrarSalir="false">
                                                        </TablaFiltrable>

                                                    </el-container>
                                                </div>
                                            </el-splitter-panel>
                                            <el-splitter-panel min="50">
                                                <div class="demo-panel panel-carac">
                                                    <el-container>
                                                        <el-header height="40px"
                                                            style=" display:flex; justify-content:center; align-items:center;">
                                                            <span
                                                                style="font-size: 18px; color: #8A2815; font-weight: bold;">
                                                                Regiones asociadas al Taxón-Caracteristica
                                                            </span>
                                                        </el-header>
                                                        <TablaFiltrable :columnas="colDefRegionCaract"
                                                            :datos="regionesCaract"
                                                            :opciones-filtro="opcionesFiltroRegCaract"
                                                            :totalItems="totalRegionesCaract"
                                                            :valoresOpcion="tiposDistribucion"
                                                            :habOpciones="habOpciones" :itemsPerPage=4
                                                            :mostrarBiblio="true" :mostrarAcci="false" :alturaTabla=288
                                                            :highlight-current-row="true" :mostrarNuevo="false"
                                                            :mostrarGuardar="true" :mostrarEditar="true"
                                                            :mostrarBorrar="false" :mostrarSalir="false">
                                                        </TablaFiltrable>
                                                    </el-container>
                                                </div>
                                            </el-splitter-panel>
                                            <el-splitter-panel min="50">
                                                <div class="demo-panel panel-nomcomun">
                                                    <el-container>
                                                        <el-header height="40px"
                                                            style=" display:flex; justify-content:center; align-items:center;">
                                                            <span
                                                                style="font-size: 18px; color: #8A2815; font-weight: bold;">
                                                                Regiones asociadas al Taxón-Nombre Comun
                                                            </span>
                                                        </el-header>
                                                        <TablaFiltrable :columnas="colDefRegionNomCom"
                                                            :datos="regionesNomCom"
                                                            :opciones-filtro="opcionesFiltroRegCaract"
                                                            :totalItems="totalRegionesNomCom" :itemsPerPage=5
                                                            :mostrarBiblio="true" :mostrarAcci="false" :alturaTabla=304
                                                            :highlight-current-row="true" :mostrarNuevo="false"
                                                            :mostrarEditar="false" :mostrarBorrar="false"
                                                            :mostrarSalir="false">
                                                        </TablaFiltrable>
                                                    </el-container>
                                                </div>
                                            </el-splitter-panel>
                                        </el-splitter>
                                    </div>
                                </el-tab-pane>
                                <el-tab-pane label="Regiones3" name="Region4">
                                    <div style="height: 500px; box-shadow: var(--el-border-color-light) 0px 0px 10px">
                                        <el-splitter lazy>
                                            <el-splitter-panel collapsible min="50">
                                                <div class="demo-tree panel-nombre">
                                                    <el-tree class="tree-full" :data="todasRegiones"
                                                        :props="defaultProps">
                                                        <template #default="{ node, data }">
                                                            <Logo class="tree-node-logo"
                                                                :rutaCategoria="data.Biblio.url" />
                                                            <span :class="claseOrigen(data)">
                                                                {{ data.Region }}
                                                                <template v-if="data.TipoDistribucion">
                                                                    ({{ data.TipoDistribucion.descripcion }})
                                                                </template>
                                                            </span>
                                                        </template>
                                                    </el-tree>
                                                </div>
                                            </el-splitter-panel>
                                            <el-splitter-panel collapsible min="50">
                                                <div class="demo-panel panel-carac">
                                                    Aqui puedo mostar el detalle
                                                </div>
                                            </el-splitter-panel>
                                        </el-splitter>
                                    </div>
                                </el-tab-pane>
                            </el-tabs>
                        </el-main>
                    </el-container>
                </div>
            </el-card>


            <DialogForm v-model="dialogFormVisibleCaract" :botCerrar="true" :pressEsc="false" :width="'83%'">
                <CuerpoCaracteristicas :modal="true" @cerrar="cerrarCarac" :treeDataProp="treeDataProp"
                    :flatTreeDataProp="flatTreeDataProp" />
            </DialogForm>
            <DialogForm v-model="dialogFormVisibleReg" :botCerrar="true" :pressEsc="false" :width="'83%'">
                <CuerpoRegion :modal="true" @cerrar="cerrarReg" :treeDataProp="treeRegionDataProp"
                    :tiposDeRegionProp="tiposDeRegionProp" :tiposDeRegionTreeProp="tiposDeRegionTreeProp" />
            </DialogForm>
            <DialogForm v-model="dialogFormVisibleNomCom" :botCerrar="true" :pressEsc="false" :width="'83%'">
                <CuerpoNombreCom :modal="true" @cerrar="cerrarNomCom" />
            </DialogForm>

            <DialogForm v-model="dialogFormVisibleRelNomCom" :botCerrar="true" :pressEsc="false" :width="'90%'">
                <RelNomComun :modal="true" :taxonActual=props.taxonAct @cerrar="cerrarRelNomCom" />
            </DialogForm>

            <DialogForm v-model="dialogFormVisibleRelCaract" :botCerrar="true" :pressEsc="false" :width="'90%'">
                <RelCaract :modal="true" :taxonActual=props.taxonAct @cerrar="cerrarRelCaract" />
            </DialogForm>

            <DialogForm v-model="dialogResumenRegionesVisible" :botCerrar="true" :pressEsc="true" :width="'85%'">
                <div style="height: 820px; background-color: #fff; display: flex; flex-direction: column; gap: 15px;">

                    <el-header class="header">
                        <div class="header-content">
                            <h1 class="titulo">Asociación de nombre comun - región - bibliografía</h1>
                        </div>
                    </el-header>

                    <div style="display: flex; justify-content: space-between; align-items: center; padding: 0 5px;">
                        <span style="font-size: 18px; color: #8A2815; font-weight: bold;">
                            {{ props.taxonAct.label }}
                        </span>
                        <BotonSalir accion="cerrar" @salir="cerrarModalesResumen" />
                    </div>

                    <div style="flex: 1; min-height: 0;">
                        <el-splitter style="height: 100%; border: 1px solid #ddd; border-radius: 8px;">
                            <el-splitter-panel :min="20" :size="'25%'">
                                <div class="table-wrapper"
                                    style="height: 100%; display: flex; flex-direction: column; border-right: 1px solid #eee;">
                                    <div
                                        style="background-color: #f2b0b0; padding: 10px; text-align: center; border-bottom: 1px solid #ddd;">
                                        <span style="color: #8A2815; font-weight: bold;">Nombres Comunes</span>
                                    </div>
                                    <div style="flex: 1; padding: 10px; overflow: auto;">
                                        <TablaFiltrable :columnas="columnasDefinidasModal" ref="refTablaNomComunModal"
                                            :datos="tablaNomComunAsociados" :permitirSinSeleccion="true"
                                            :totalItems="totalRegNomComun" :alturaTabla="500"
                                            :highlight-current-row="true" row-key="IdNomComun" :mostrarBiblio="false"
                                            :mostrarNuevo="false" :mostrarBorrar="false" :mostrarSalir="false"
                                            :mostrarEditar="false" @row-click="clickNomCom" />
                                    </div>
                                </div>
                            </el-splitter-panel>

                            <el-splitter-panel>
                                <el-splitter>
                                    <el-splitter-panel :min="20" :size="'33%'">
                                        <div class="table-wrapper"
                                            style="height: 100%; display: flex; flex-direction: column; border-right: 1px solid #eee;">
                                            <div
                                                style="background-color: #bae1f2; padding: 10px; text-align: center; border-bottom: 1px solid #ddd;">
                                                <span style="color: #4b7a94; font-weight: bold;">Regiones</span>
                                            </div>
                                            <div style="flex: 1; padding: 10px; overflow: auto;">
                                                <TablaFiltrable :columnas="columnasDefinidasRegNomCom"
                                                    ref="refTablaRegionesModal" :datos="tablaNomComunReg"
                                                    :totalItems="totalRegionNomComun" :permitirSinSeleccion="true"
                                                    :alturaTabla="500" :highlight-current-row="true"
                                                    :mostrarBiblio="false" row-key="IdRegion" :mostrarNuevo="false"
                                                    :mostrarBorrar="false" :mostrarSalir="false" :mostrarEditar="false"
                                                    @row-click="clickRegNomCom" />
                                            </div>
                                        </div>
                                    </el-splitter-panel>

                                    <el-splitter-panel>
                                        <el-splitter layout="vertical">
                                            <el-splitter-panel :min="50" :size="'40%'">
                                                <div class="table-wrapper"
                                                    style="height: 100%; display: flex; flex-direction: column;">
                                                    <div
                                                        style="background-color: #f4f4bf; padding: 10px; text-align: center; border-bottom: 1px solid #ddd; position: relative;">
                                                        <span
                                                            style="color: #856404; font-weight: bold;">Bibliografía</span>
                                                    </div>
                                                    <div style="flex: 1; padding: 10px; overflow: auto;">
                                                        <TablaFiltrable ref="refTablaBiblioModal"
                                                            :columnas="colDefBiblioFinal" row-key="IdBibliografia"
                                                            :permitirSinSeleccion="true" :datos="tablaBibliografiasRel"
                                                            :totalItems="totalBibliografiasRel" :alturaTabla="350"
                                                            :highlight-current-row="true" :mostrarNuevo="true"
                                                            :mostrarEditar="true" :mostrarBorrar="true"
                                                            :mostrarSalir="false" :mostrarGuardar="true"
                                                            :deshabilitarGuardar="botonGuardarDeshabilitado"
                                                            @row-click="clickBiblioRel" @nuevo-item="abrirBiblio"
                                                            @eliminar-item="eliminarBiblioRel"
                                                            @editar-item="habilitarEdicionObs"
                                                            @guardar="guardarCambiosObs" />
                                                    </div>
                                                </div>
                                            </el-splitter-panel>

                                            <el-splitter-panel :size="'10%'">
                                                <div
                                                    style="padding: 15px; background: #fff; border-top: 2px solid #eee; height: 100%;">
                                                    <p
                                                        style="font-size: 13px; color: #333; margin-bottom: 8px; font-weight: bold;">
                                                        Observaciones de nombre común - región - bibliografía
                                                    </p>
                                                    <div style="display: flex; align-items: flex-start; gap: 10px;">
                                                        <el-input v-model="observaciones" type="textarea" :rows="3"
                                                            placeholder="Observaciones" style="flex: 1;"
                                                            :disabled="!editandoObs" />
                                                    </div>
                                                </div>
                                            </el-splitter-panel>
                                        </el-splitter>
                                    </el-splitter-panel>
                                </el-splitter>
                            </el-splitter-panel>
                        </el-splitter>
                    </div>
                </div>
            </DialogForm>


            <DialogForm v-model="dialogResumenCaractVisible" :botCerrar="true" :width="'90%'">
                <div style="height: 760px; background-color: #fff; display: flex; flex-direction: column; gap: 15px;">
                    <el-header class="header">
                        <div class="header-content">
                            <h1 class="titulo">Asociación de característica - región - bibliografía</h1>
                        </div>
                    </el-header>

                    <div
                        style="display: flex; justify-content: space-between; align-items: center; padding: 0 5px; margin-top: 20px; margin-bottom: 20px;">
                        <el-row :gutter="21">
                            <el-col :span="18">
                                <span style="font-size: 18px; color: #8A2815; font-weight: bold;">
                                    {{ props.taxonAct.label }}
                                </span>
                            </el-col>
                            <el-col :span="5">
                                <div style="display: flex; gap: 5px; justify-content: flex-end; margin-left: 420px;">
                                    <BotonSalir accion="cerrar" @salir="closeDialog"
                                        style="flex-shrink: 0; min-width: max-content;" />
                                </div>
                            </el-col>
                        </el-row>
                    </div>
                    <el-splitter lazy>
                        <el-splitter-panel :size="'22%'">
                            <div class="panel-carac table-wrapper" style="height: 100%; border-radius: 8px;">
                                <el-header height="40px"
                                    style="display:flex; justify-content:center; align-items:center;">
                                    <span
                                        style="font-size: 16px; color: #8A2815; font-weight: bold;">Característica</span>
                                </el-header>
                                <TablaFiltrable :columnas="columnasDefinidasCaract" :datos="tablaCaracteristicas"
                                    :totalItems="totalRegCaract" :alturaTabla="450" :highlight-current-row="true"
                                    @row-click="clickCaract" :mostrarBiblio="false" :mostrarNuevo="false"
                                    :mostrarBorrar="false" :mostrarSalir="false" :mostrarEditar="false" />
                            </div>
                        </el-splitter-panel>

                        <el-splitter-panel :size="'24%'">
                            <div class="panel-nombre table-wrapper" style="height: 100%; border-radius: 8px;">
                                <el-header height="40px"
                                    style="display:flex; justify-content:center; align-items:center;">
                                    <span style="font-size: 16px; color: #8A2815; font-weight: bold;">Regiones</span>
                                </el-header>
                                <TablaFiltrable :columnas="columnasDefinidasRegCaract" :datos="tablaCaractReg"
                                    :totalItems="totalRegionCaract" :alturaTabla="450" :highlight-current-row="true"
                                    @row-click="clickRegCaract" :mostrarBiblio="false" :mostrarNuevo="false"
                                    :mostrarBorrar="false" :mostrarSalir="false" :mostrarEditar="false" />
                            </div>
                        </el-splitter-panel>

                        <el-splitter-panel>
                            <el-splitter layout="vertical">
                                <el-splitter-panel :size="'20%'">
                                    <div class="panel-nomcomun table-wrapper" style="height: 100%; border-radius: 8px;">
                                        <el-header height="40px"
                                            style="display:flex; justify-content:center; align-items:center;">
                                            <span style="font-size: 16px; color: #8A2815; font-weight: bold;">
                                                Bibliografía
                                            </span>
                                        </el-header>
                                        <TablaFiltrable :columnas="colDefBiblioFinal" :datos="tablaBibliografiasRel"
                                            :totalItems="totalBibliografiasRel" :alturaTabla="368"
                                            :highlight-current-row="true" :mostrarNuevo="true" :mostrarEditar="false"
                                            :mostrarBorrar="true" :mostrarSalir="false" @nuevo-item="abrirBiblio"
                                            @eliminar-item="eliminarBiblioRel" />
                                    </div>
                                </el-splitter-panel>

                                <el-splitter-panel :size="'5%'">
                                    <div
                                        style="padding: 15px; background: #fff; border-top: 2px solid #eee; height: 100%;">
                                        <p style="font-size: 13px; color: #333; margin-bottom: 8px; font-weight: bold;">
                                            Observaciones de cracterística- región - bibliografía
                                        </p>
                                        <div style="display: flex; align-items: flex-start; gap: 10px;">
                                            <el-input v-model="observaciones" type="textarea" :rows="3"
                                                placeholder="Observaciones" style="flex: 1;" disabled />
                                            <el-button type="warning" icon="el-icon-document-checked" circle
                                                style="background-color: #e6a23c; border-color: #e6a23c; padding: 12px;"
                                                @click="Guardar" />
                                        </div>
                                    </div>
                                </el-splitter-panel>
                            </el-splitter>
                        </el-splitter-panel>

                    </el-splitter>
                </div>
            </DialogForm>

            <DialogForm v-model="dialogResumenCaractSoloVisible" :botCerrar="true" :width="'80%'">
                <div
                    style="height: 830px; padding: 10px; background-color: #fff; display: flex; flex-direction: column;">

                    <el-header class="header">
                        <div class="header-content">
                            <h1 class="titulo">Asociación de característica - bibliografía</h1>
                        </div>
                    </el-header>

                    <div style="padding: 15px 5px;">
                        <span style="font-size: 18px; color: #8A2815; font-weight: bold;">
                            {{ props.taxonAct.label }}
                        </span>
                    </div>

                    <div style="flex: 1; min-height: 0;">
                        <el-splitter style="height: 100%; border: 1px solid #ddd; border-radius: 8px;">
                            <el-splitter-panel :min="30" :size="'35%'">
                                <div
                                    style="height: 100%; display: flex; flex-direction: column; border-right: 1px solid #eee;">
                                    <div
                                        style="background-color: #bae1f2; padding: 10px; text-align: center; border-bottom: 1px solid #ddd;">
                                        <span style="color: #4b7a94; font-weight: bold;">Característica</span>
                                    </div>
                                    <div style="flex: 1; padding: 10px; overflow: auto;">
                                        <TablaFiltrable :columnas="columnasDefinidasCaract"
                                            :datos="tablaCaracteristicas" :totalItems="totalRegCaract"
                                            :alturaTabla="480" :highlight-current-row="true" @row-click="clickCaract"
                                            :mostrarBiblio="false" :mostrarNuevo="false" :mostrarBorrar="false"
                                            :mostrarSalir="false" :mostrarEditar="false" />
                                    </div>
                                </div>
                            </el-splitter-panel>

                            <el-splitter-panel>
                                <el-splitter layout="vertical">
                                    <el-splitter-panel :size="'70%'">
                                        <div style="height: 100%; display: flex; flex-direction: column;">
                                            <div
                                                style="background-color: #f4f4bf; padding: 10px; text-align: center; border-bottom: 1px solid #ddd; position: relative;">
                                                <span style="color: #856404; font-weight: bold;">Bibliografía</span>
                                            </div>
                                            <div style="flex: 1; padding: 10px; overflow: auto;">
                                                <TablaFiltrable :columnas="colDefBiblioFinal"
                                                    :datos="tablaBibliografiasRel" :totalItems="totalBibliografiasRel"
                                                    :alturaTabla="320" :highlight-current-row="true"
                                                    :mostrarNuevo="true" :mostrarEditar="false" :mostrarBorrar="true"
                                                    :mostrarSalir="false" @nuevo-item="abrirBiblio"
                                                    @eliminar-item="eliminarBiblioRel" @row-click="clickBiblioRel" />
                                            </div>
                                        </div>
                                    </el-splitter-panel>

                                    <el-splitter-panel :size="'20%'">
                                        <div
                                            style="padding: 15px; background: #fff; border-top: 2px solid #eee; height: 100%;">
                                            <p
                                                style="font-size: 13px; color: #333; margin-bottom: 8px; font-weight: bold;">
                                                Observaciones característica - bibliografía</p>
                                            <div style="display: flex; align-items: flex-start; gap: 10px;">
                                                <el-input v-model="observaciones" type="textarea" :rows="3" disabled
                                                    placeholder="Observaciones" style="flex: 1;" />
                                            </div>
                                        </div>
                                    </el-splitter-panel>
                                </el-splitter>
                            </el-splitter-panel>

                        </el-splitter>
                    </div>
                </div>
            </DialogForm>




            <DialogForm v-model="dialogFormVisibleBiblio" :botCerrar="true" :pressEsc="false" :width="'85%'">
                <CuerpoBibliografia :isModal="true" :traspaso="true" :biblioAct="idsBibliografiasActuales"
                    @cerrarBiblio="cerrarBiblio" @asociar="vincularInmediato" />
            </DialogForm>

            <Teleport to="body">
                <NotificacionExitoErrorModal :visible="notificacionVisible" :titulo="notificacionTitulo"
                    :mensaje="notificacionMensaje" :tipo="notificacionTipo" :duracion="5000"
                    @close="notificacionVisible = false" />
            </Teleport>

        </div>
    </template>

<script setup>
import { Management, ChatLineRound, Location } from '@element-plus/icons-vue';
import BotonTraspaso from '@/Components/Biotica/BtnTraspaso.vue';
import BotonSalir from '@/Components/Biotica/SalirButton.vue';
import BotonCaract from '@/Components/Biotica/BtnCaracteristicas.vue';
import BotonRegiones from '@/Components/Biotica/BtnRegiones.vue';
import BotonNomComun from '@/Components/Biotica/BtnNomComunes.vue';
import NuevoButton from '@/Components/Biotica/NuevoButton.vue';
import DialogForm from '@/Components/Biotica/DialogGeneral.vue';
import CuerpoCaracteristicas from '@/Pages/Socat/Caracteristicas/CuerpoCaracteristicas.vue';
import CuerpoRegion from '@/Pages/Socat/Regiones/CuerpoRegion.vue';
import RelNomComun from '@/Pages/Socat/RelCatalogosAsociados/RelacionNomComun.vue';
import CuerpoNombreCom from '@/Pages/Socat/Nombres/CuerpoNombreComun.vue';
import CuerpoTipoRegion from '@/Pages/Socat/TipoRegion/CuerpoTipoRegion.vue';
import CuerpoBibliografia from '@/Pages/Socat/Bibliografia/CuerpoBibliografia.vue';
import TablaFiltrable from "@/Components/Biotica/TablaFiltrable.vue";
import { onMounted, ref, watch, computed, h, nextTick } from 'vue';
import EditarButton from '@/Components/Biotica/EditarButton.vue';
import GuardarButton from '@/Components/Biotica/GuardarButton.vue';
import Logo from '@/Components/Biotica/LogoCategoria.vue';
import { ElMessageBox } from 'element-plus';
import BotonAceptar from '@/Components/Biotica/BotonAceptar.vue';
import BotonCancelar from '@/Components/Biotica/BotonCancelar.vue';
import NotificacionExitoErrorModal from "@/Components/Biotica/NotificacionExitoErrorModal.vue";
import IconoMundo from '@/Components/Biotica/IconoMundo.vue';
import RelCaract from '@/Pages/Socat/RelCatalogosAsociados/RelacionCaracteristicas.vue';
import EliminarButton from '@/Components/Biotica/EliminarButton.vue';

const cerrarModalesResumen = async () => {
    dialogResumenRegionesVisible.value = false;
    dialogResumenCaractVisible.value = false;
    dialogResumenCaractSoloVisible.value = false;
    idBiblioSeleccionada.value = null;
    editandoObs.value = false;
    await recargarNombresAsociados();
};

const editandoObs = ref(false);
const idBiblioSeleccionada = ref(null);

const botonGuardarDeshabilitado = computed(() => {
    return !editandoObs.value || !idBiblioSeleccionada.value;
});

const editandoObsGeneral = ref(false);

const habilitarEdicionObsGeneral = (row) => {
    clickNomCom(row);
    editandoObsGeneral.value = true;
};

const guardarCambiosObsGeneral = async () => {
    if (!idNomComunSeleccionado.value) {
        mostrarNotificacion("Aviso", "Por favor seleccione un nombre común", "warning");
        return;
    }
    const proceder = async () => {
        ElMessageBox.close();
        try {
            let endpoint = '';
            let payload = {};

            if (tipoSeleccion.value === 'comun') {
                endpoint = '/actualizar-obs-nomcomun-base';
                payload = {
                    IdNomComun: idNomComunSeleccionado.value,
                    Observaciones: observacionesGeneral.value
                };
            } else {
                endpoint = '/actualizar-obs-relacion-base';
                payload = {
                    IdNomComun: idNomComunSeleccionado.value,
                    IdNombre: props.taxonAct.id,
                    IdRegion: idRegionSeleccionada.value,
                    Observaciones: observacionesGeneral.value
                };
            }

            await axios.put(endpoint, payload);
            mostrarNotificacion("Modificación", "La observación fue modificada exitosamente", "success");
            editandoObsGeneral.value = false;
            await recargarNombresAsociados();

        } catch (error) {
            console.error(error);
            mostrarNotificacion("Error", "No se pudo actualizar", "error");
        }
    };

    const textoPregunta = etiquetaObservaciones.value.toLowerCase().replace(':', '');
    ElMessageBox({
        title: 'Confirmar modificación',
        showConfirmButton: false,
        showCancelButton: false,
        customClass: 'message-box-diseno-limpio',
        message: h('div', { class: 'custom-message-content' }, [
            h('div', { class: 'body-content' }, [
                h('div', { class: 'custom-warning-icon-container' }, [
                    h('div', { class: 'custom-warning-circle' }, '!')
                ]),
                h('div', { class: 'text-container' }, [
                    h('p', null, `¿Deseas guardar los cambios en las ${textoPregunta}?`)
                ])
            ]),
            h('div', { class: 'footer-buttons' }, [
                h(BotonCancelar, {
                    onClick: () => ElMessageBox.close()
                }),
                h(BotonAceptar, {
                    onClick: proceder
                }),
            ])
        ])
    }).catch(() => { });
};

const editandoObsReg = ref(false);

const habilitarEdicionObsReg = (row) => {
    clickRegNomCom(row);
    editandoObsReg.value = true;
};

const guardarCambiosObsReg = async () => {
    if (!idNomComunSeleccionado.value || !idRegionSeleccionada.value) return;

    const proceder = async () => {
        ElMessageBox.close();
        try {
            const payload = {
                IdNomComun: idNomComunSeleccionado.value,
                IdNombre: props.taxonAct.id,
                IdRegion: idRegionSeleccionada.value,
                Observaciones: observacionesRegTab.value
            };

            await axios.put('/actualizar-obs-relacion-base', payload);

            mostrarNotificacion("Modificación", "La observación fue modificada exitosamente", "success");
            editandoObsReg.value = false;

            const index = tablaNomComunReg.value.findIndex(r => (r.IdRegion || r.id) === idRegionSeleccionada.value);
            if (index !== -1) {
                tablaNomComunReg.value[index].ObservacionesReg = observacionesRegTab.value;
                tablaNomComunReg.value[index].Observaciones = observacionesRegTab.value;
            }
        } catch (error) {
            mostrarNotificacion("Error", "No se pudo actualizar", "error");
        }
    };

    ElMessageBox({
        title: 'Confirmar modificación',
        showConfirmButton: false,
        showCancelButton: false,
        customClass: 'message-box-diseno-limpio',
        message: h('div', { class: 'custom-message-content' }, [
            h('div', { class: 'body-content' }, [
                h('div', { class: 'custom-warning-icon-container' }, [h('div', { class: 'custom-warning-circle' }, '!')]),
                h('div', { class: 'text-container' }, [
                    h('p', null, "¿Deseas guardar los cambios en las observaciones de esta región?")
                ])
            ]),
            h('div', { class: 'footer-buttons' }, [
                h(BotonCancelar, { onClick: () => ElMessageBox.close() }),
                h(BotonAceptar, { onClick: proceder }),
            ])
        ])
    }).catch(() => { });
};




const habilitarEdicionObs = (row) => {
    if (editandoObs.value && idBiblioSeleccionada.value) {
        const registroEnTabla = tablaBibliografiasRel.value.find(
            b => (b.IdBibliografia || b.id) === idBiblioSeleccionada.value
        );
        const valorOriginal = registroEnTabla ? (registroEnTabla.Observaciones || "") : "";
        if (observaciones.value !== valorOriginal) {
            mostrarNotificacion(
                "Aviso",
                "Primero debe guardar los cambios realizados en las observaciones antes de volver a editar o cambiar de registro.",
                "warning"
            );
            return;
        }
    }
    clickBiblioRel(row);
    nextTick(() => {
        editandoObs.value = true;
    });
};

const props = defineProps({
    taxonAct: {
        type: Object
    },
});

const defaultProps = {
    children: 'children',
    label: 'Region',
}

const observacionesGeneral = ref('');
const observacionesCaractGral = ref('');

const notificacionVisible = ref(false);
const notificacionTitulo = ref("");
const notificacionMensaje = ref("");
const notificacionTipo = ref("success");

const mostrarNotificacion = (titulo, mensaje, tipo = "success") => {
    notificacionTitulo.value = titulo;
    notificacionMensaje.value = mensaje;
    notificacionTipo.value = tipo;
    notificacionVisible.value = true;
};


const guardarCambiosObs = async () => {
    if (!idBiblioSeleccionada.value) {
        mostrarNotificacion("Aviso", "Seleccione una bibliografía primero", "warning");
        return;
    }
    const procederConGuardado = async () => {
        ElMessageBox.close();
        try {
            const payload = {
                IdNomComun: idNomComunSeleccionado.value,
                IdRegion: idRegionSeleccionada.value,
                IdNombre: props.taxonAct.id,
                IdBibliografia: idBiblioSeleccionada.value,
                Observaciones: observaciones.value
            };

            const response = await axios.put('/actualizar-obs-nomcomun-region', payload);
            if (response.status === 200) {
                mostrarNotificacion("Modificación", "La observación fue modificada correctamente", "success");
                editandoObs.value = false;
                const index = tablaBibliografiasRel.value.findIndex(
                    b => (b.IdBibliografia || b.id) === idBiblioSeleccionada.value
                );
                if (index !== -1) {
                    tablaBibliografiasRel.value[index].Observaciones = observaciones.value;
                }
            }
        } catch (error) {
            console.error(error);
            mostrarNotificacion("Error", "No se pudo guardar la observación", "error");
        }
    };

    ElMessageBox({
        title: 'Confirmar modificación',
        showConfirmButton: false,
        showCancelButton: false,
        customClass: 'message-box-diseno-limpio',
        message: h('div', { class: 'custom-message-content' }, [
            h('div', { class: 'body-content' }, [
                h('div', { class: 'custom-warning-icon-container' }, [
                    h('div', { class: 'custom-warning-circle' }, '!')
                ]),
                h('div', { class: 'text-container' }, [
                    h('p', null, "¿Estás seguro de que deseas guardar los cambios realizados en las observaciones?")
                ])
            ]),
            h('div', { class: 'footer-buttons' }, [
                h(BotonCancelar, { onClick: () => ElMessageBox.close() }),
                h(BotonAceptar, { onClick: procederConGuardado }),
            ])
        ])
    }).catch(() => {
    });
};


const clickBiblioRel = (row) => {
    if (!row) {
        idBiblioSeleccionada.value = null;
        return;
    }
    idBiblioSeleccionada.value = row.IdBibliografia || row.id || row.IdCatBiblio;
    observaciones.value = row.Observaciones || row.observaciones || "";
    console.log("ID Bibliografía seleccionado:", idBiblioSeleccionada.value);
};


const dialogResumenRegionesVisible = ref(false);
const dialogResumenCaractVisible = ref(false);

const abrirResumenRegiones = () => {
    idNomComunSeleccionado.value = null;
    idRegionSeleccionada.value = null;
    idBiblioSeleccionada.value = null;
    tablaNomComunReg.value = [];
    totalRegionNomComun.value = 0;
    tablaBibliografiasRel.value = [];
    totalBibliografiasRel.value = 0;
    observaciones.value = "";
    editandoObs.value = false;
    dialogResumenRegionesVisible.value = true;

    setTimeout(() => {
        const tablas = [refTablaNomComunModal, refTablaRegionesModal, refTablaBiblioModal];
        tablas.forEach(t => {
            if (t.value && typeof t.value.clearCurrentRow === 'function') {
                t.value.clearCurrentRow();
            }
        });
    }, 200);
};


const abrirResumenCaract = () => {
    dialogResumenCaractVisible.value = true;
};

const dialogResumenCaractSoloVisible = ref(false);

const abrirResumenCaractSolo = () => {
    dialogResumenCaractSoloVisible.value = true;
    if (idCaractSeleccionada.value) {
        cargarBibliografiasRelCaractSolo();
    }
};


const cargarBibliografiasRelCaractSolo = async () => {
    if (!idCaractSeleccionada.value || !props.taxonAct.id) return;

    try {
        const response = await axios.get('/obtener-biblio-caract-solo', {
            params: {
                IdNombre: props.taxonAct.id,
                IdCatNombre: idCaractSeleccionada.value,
            }
        });

        if (response.status === 200) {
            tablaBibliografiasRel.value = response.data;
            totalBibliografiasRel.value = response.data.length;
            idRegionCaractSeleccionada.value = null;
            idTipoDistSeleccionada.value = null;
        }
    } catch (error) {
        console.error("Error al cargar bibliografía general de la característica:", error);
    }
};

const claseOrigen = (data) => {
    switch (data.origen) {

        case 'nombre':
            return 'origen-rojo';

        case 'caracteristica':
            return 'origen-verde';

        case 'nomComun':
            return 'origen-amarillo';
    }
}

const idsBibliografiasActuales = computed(() => {
    return tablaBibliografiasRel.value.map(b => b.IdBibliografia || b.id);
});


const vincularInmediato = async (idBiblio) => {
    if (tabInicial.value === 'Caracteristicas') {
        if (!idCaractSeleccionada.value) {
            alert("Seleccione primero una característica de la lista de la izquierda.");
            return;
        }
        await vincularInmediatoCaract(idBiblio);
    }

    else if (tabInicial.value === 'NomComun') {
        if (!idNomComunSeleccionado.value || !idRegionSeleccionada.value || !props.taxonAct.id) {
            alert("Faltan datos para realizar la asociación (Nombre común o Región).");
            return;
        }

        try {
            const response = await axios.post('/asociar-biblio-nomcomun-region', {
                IdNomComun: idNomComunSeleccionado.value,
                IdRegion: idRegionSeleccionada.value,
                IdNombre: props.taxonAct.id,
                idsBibliografias: [idBiblio]
            });

            if (response.status === 200) {
                await cargarBibliografiasRelacionadas();
            }
        } catch (error) {
            console.error("ERROR AL VINCULAR NOM COMUN:", error.response?.data);
        }
    }
};


const vincularInmediatoCaract = async (idBiblio) => {
    try {
        if (idRegionCaractSeleccionada.value) {
            await axios.post('/asociar-biblio-caract-region', {
                IdNombre: props.taxonAct.id,
                IdCatNombre: idCaractSeleccionada.value,
                IdRegion: idRegionCaractSeleccionada.value,
                IdTipoDistribucion: idTipoDistSeleccionada.value,
                IdBibliografia: idBiblio,
                usuario: 'sistema'
            });
            await cargarBibliografiasRelCaract();
        } else {
            await axios.post('/asociar-biblio-caract-solo', {
                IdNombre: props.taxonAct.id,
                IdCatNombre: idCaractSeleccionada.value,
                IdBibliografia: idBiblio,
                usuario: 'sistema'
            });
            await cargarBibliografiasRelCaractSolo();
        }
    } catch (error) {
        alert("Error al guardar bibliografía.");
    }
};



const dialogFormVisibleBiblio = ref(false);

const abrirBiblio = () => {
    if (tabInicial.value === 'NomComun') {
        if (tablaNomComunAsociados.value.length === 0) {
            mostrarNotificacion("Aviso", "Primero debe asociar nombres comunes junto sus regiones para asociar bibliografías", "warning");
            return;
        }
        if (!idNomComunSeleccionado.value) {
            mostrarNotificacion("Aviso", "Por favor selecciona un nombre común", "warning");
            return;
        }
        if (!idRegionSeleccionada.value) {
            mostrarNotificacion("Aviso", "Por favor selecciona una región para agregar bibliografía", "warning");
            return;
        }
    }
    dialogFormVisibleBiblio.value = true;
};

const cerrarBiblio = async (idsSeleccionados) => {
    dialogFormVisibleBiblio.value = false;
    if (!idsSeleccionados || idsSeleccionados.length === 0) return;

    if (tabInicial.value === 'Caracteristicas') {
        for (const idBiblio of idsSeleccionados) {
            await vincularInmediatoCaract(idBiblio);
        }
    }
    else if (tabInicial.value === 'NomComun') {
        if (!idNomComunSeleccionado.value || !idRegionSeleccionada.value) {
            alert("Error: No se ha seleccionado un Nombre Común o una Región correctamente.");
            return;
        }

        try {
            await axios.post('/asociar-biblio-nomcomun-region', {
                IdNomComun: idNomComunSeleccionado.value,
                IdRegion: idRegionSeleccionada.value,
                idsBibliografias: idsSeleccionados
            });
            await cargarBibliografiasRelacionadas();
        } catch (error) {
            console.error("Error al asociar NomComun:", error.response?.data || error);
        }
    }
};

const activarEdicionGeneral = () => {
    if (!nodoSeleccionadoArbol.value) {
        mostrarNotificacion("Aviso", "Por favor seleccione un nombre común o una región para editar", "warning");
        return;
    }

    if (editandoObsGeneral.value && observacionesGeneral.value !== valorOriginalObsGeneral.value) {
        mostrarNotificacion(
            "Aviso",
            "Primero debe guardar los cambios realizados en las observaciones antes de volver a habilitar la edición.",
            "warning"
        );
        return;
    }

    editandoObsGeneral.value = true;
};
const valorOriginalObsGeneral = ref('');
const nodoSeleccionadoArbol = ref(null);
const tablaNomComunPrincipalRef = ref(null);
const tipoSeleccion = ref('');
const tablaNomComunAsociados = ref([]);
const tabInicial = ref("NomComun");
const idRegionSeleccionada = ref(null);
const idCaractSeleccionada = ref(null);
const idRegionCaractSeleccionada = ref(null);
const idTipoDistSeleccionada = ref(null);
const tablaBiblioCaract = ref([]);
const totalBiblioCaract = ref(0);
const nombresAsociadosTaxon = ref([]);

const localTreeData = ref([]);
const localTreeDataTaxon = ref([]);
const flatTreeDataProp = ref([]);
const treeDataProp = ref([]);
const dialogFormVisibleCaract = ref(false);
const dialogFormVisibleReg = ref(false);
const dialogFormVisibleNomCom = ref(false);

const dialogFormVisibleRelNomCom = ref(false);
const dialogFormVisibleRelCaract = ref(false);

const treeRegionDataProp = ref([]);
const tiposDeRegionProp = ref([]);
const tiposDeRegionTreeProp = ref([]);
const etiquetaObs = ref('');

const observacionesRegTab = ref('');
const observaciones = ref('');

const tablaNomComun = ref([]);
const tablaNomComunReg = ref([]);
const tablaCaracteristicas = ref([]);
const tablaCaractReg = ref([]);

const totalRegCaract = ref(0);
const totalRegionCaract = ref(0);

const tiposDistribucion = ref([]);
const habOpciones = ref(true);

const regionesNombre = ref([]);
const regionesCaract = ref([]);
const regionesNomCom = ref([]);

const todasRegiones = ref([]);
const totalRegiones = ref(0);

const totalRegionesNom = ref(0);
const totalRegionesCaract = ref(0);
const totalRegionesNomCom = ref(0);
const idNomComunSeleccionado = ref(null);
const tablaRegionesNomComRef = ref(null);

const tiposRegionTreeData = ref([]);
const localTreeDataReg = ref([]);
const selectedTipoRegionNode = ref(null);
const activePathIds = ref([]);
const tiposRegionTreeRef = ref(null);
const treeRef = ref(null);
const treeKey = ref(0);
const expandedTipoRegionKeys = ref([]);
const selectedNode = ref(null);
const esModalTipoRegionVisible = ref(false);
const todosLosTiposDeRegion = ref([]);
const todosLosTiposDeRegionTree = ref([]);
let ultimoEventoExpandTime = 0;
const contRegNomCom = ref(0);


const refTablaNomComunModal = ref(null);
const refTablaRegionesModal = ref(null);
const refTablaBiblioModal = ref(null);

const etiquetaObservaciones = computed(() => {
    if (tipoSeleccion.value === 'comun') return 'Observaciones de nombre común:';
    if (tipoSeleccion.value === 'region') return 'Observaciones de nombre común - región:';
    return 'Observaciones:';
});


const placeholderObservaciones = computed(() => {
    return tipoSeleccion.value === 'region'
        ? 'Observaciones de nombre común - región...'
        : 'Observaciones de nombre común...';
});

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


const eliminarBiblioRel = async (row) => {
    const idBiblio = row.IdBibliografia || row.id;
    const mensajeConfirmacion = "¿Está seguro de eliminar la bibliografía seleccionada? Esta acción no se puede revertir.";

    const procederConEliminacion = async () => {
        ElMessageBox.close();
        try {
            if (tabInicial.value === 'Caracteristicas') {
                if (idRegionCaractSeleccionada.value) {
                    const payloadCarac = {
                        IdNombre: props.taxonAct.id,
                        IdCatNombre: idCaractSeleccionada.value,
                        IdRegion: idRegionCaractSeleccionada.value,
                        IdTipoDistribucion: idTipoDistSeleccionada.value,
                        IdBibliografia: idBiblio
                    };
                    await axios.post('/eliminar-biblio-caract-region', payloadCarac);
                    await cargarBibliografiasRelCaract();
                }
                else {
                    const payloadCaracSolo = {
                        IdNombre: props.taxonAct.id,
                        IdCatNombre: idCaractSeleccionada.value,
                        IdBibliografia: idBiblio
                    };
                    await axios.post('/eliminar-biblio-caract-solo', payloadCaracSolo);
                    await cargarBibliografiasRelCaractSolo();
                }
            }
            else if (tabInicial.value === 'NomComun') {
                const payloadNomCom = {
                    IdNomComun: idNomComunSeleccionado.value,
                    IdRegion: idRegionSeleccionada.value,
                    IdNombre: props.taxonAct.id,
                    IdBibliografia: idBiblio
                };
                await axios.post('/eliminar-biblio-nomcomun-region', payloadNomCom);
                await cargarBibliografiasRelacionadas();
            }

            observaciones.value = "";
            idBiblioSeleccionada.value = null;
            editandoObs.value = false;

            if (refTablaBiblioModal.value && refTablaBiblioModal.value.clearCurrentRow) {
                refTablaBiblioModal.value.clearCurrentRow();
            }

            mostrarNotificacion("Eliminación", "La bibliografía ha sido eliminada correctamente.", "success");

        } catch (error) {
            console.error("Error al eliminar:", error);
            mostrarNotificacion("Error", "No se pudo eliminar el registro de la base de datos.", "error");
        }
    };

    ElMessageBox({
        title: 'Confirmar eliminación',
        showConfirmButton: false,
        showCancelButton: false,
        customClass: 'message-box-diseno-limpio',
        message: h('div', { class: 'custom-message-content' }, [
            h('div', { class: 'body-content' }, [
                h('div', { class: 'custom-warning-icon-container' }, [
                    h('div', { class: 'custom-warning-circle' }, '!')
                ]),
                h('div', { class: 'text-container' }, [
                    h('p', null, mensajeConfirmacion)
                ])
            ]),
            h('div', { class: 'footer-buttons' }, [
                h(BotonCancelar, { onClick: () => ElMessageBox.close() }),
                h(BotonAceptar, { onClick: procederConEliminacion }),
            ])
        ])
    }).catch(() => { });
};

const emit = defineEmits(['cerrar']);

const closeDialog = () => {
    tabInicial.value = "NomComun";
    dialogResumenRegionesVisible.value = false;
    dialogResumenCaractVisible.value = false;
    dialogResumenCaractSoloVisible.value = false;
    dialogFormVisibleBiblio.value = false;
    idNomComunSeleccionado.value = null;
    idRegionSeleccionada.value = null;
    idCaractSeleccionada.value = null;
    observacionesGeneral.value = "";
    editandoObsGeneral.value = false;
    emit('cerrar');
};

const columnasDefinidasNomCom = ref([
    {
        prop: 'NomComun', label: 'Nombre común', minWidth: '80',
        sortable: true, filtrable: true, align: 'left'
    },
    {
        prop: 'Lengua', label: 'Lengua', minWidth: '70',
        sortable: true, filtrable: true, align: 'left'
    },

]);

const columnasDefinidas = ref([
    {
        prop: 'NombreComun', label: 'Nombre comun', minWidth: '120',
        align: 'left', tipo: 'texto', filtrable: true
    },
    {
        prop: 'Lengua', label: 'Lengua', minWidth: '250',
        align: 'left', tipo: 'texto', filtrable: true
    }
]);


const columnasDefinidasModal = ref([
    {
        prop: 'NombreComun', label: 'Nombre comun', minWidth: '120',
        align: 'left', tipo: 'texto', filtrable: true
    },
]);

const columnasDefinidasRegNomCom = ref([
    {
        prop: 'Region', label: 'Región', minWidth: '120',
        align: 'left', tipo: 'Texto', filtrable: true
    }
]);

const opcionesFiltroNomComun = ref([
    { label: 'Nombre común', value: 'NomComun' },
    { label: 'Lengua', value: 'Lengua' },
    { label: 'Observaciones', value: 'Observaciones' }
]);

const opcionesFiltroRegNomComun = ref([
    { label: 'Region', value: 'Region' },
]);

const opcionesFiltroRegCaract = ref([
    { label: 'Region', value: 'Region' },
    { label: 'Tipo Distribución', value: 'TipDistribucion' },
]);

const columnasDefinidasCaract = ref([
    {
        prop: 'Caracteristica', label: 'Caracteristica', minWidth: '100',
        align: 'left', tipo: 'texto', filtrable: true
    }
]);

const opcionesFiltroCaract = ref([
    { label: 'Caracteristica', value: 'Caracteristica' }
]);

const columnasDefinidasRegCaract = ref([
    {
        prop: 'Region', label: 'Región', minWidth: '120',
        align: 'left', tipo: 'Texto', filtrable: true
    },
    {
        prop: 'TipDistribucion', label: 'Tipo Distribucion', minWidth: '120',
        align: 'left', tipo: 'lista', filtrable: true
    },
    {
        prop: 'Biblio', label: '', minWidth: '55', align: 'left',
        tipo: 'imagenTexto', filtrable: false
    },
]);

const actObsCaract = ref(true);
const rowCaract = ref([]);
const actObsCaractReg = ref(true);
const idTipoDistMod = ref(0);
const rowCaractReg = ref([]);
const obsCaractReg = ref("");
const tipDistAct = ref(0);

const colDefRegionNombre = ref([
    {
        prop: 'Region', label: 'Región', minWidth: '120',
        align: 'left', tipo: 'Texto', filtrable: true
    },
    {
        prop: 'TipoDistribucion', label: 'Tipo Distribucion', minWidth: '120',
        align: 'left', tipo: 'lista', filtrable: true
    },
    {
        prop: 'Biblio', label: '', minWidth: '55', align: 'left',
        tipo: 'imagenTexto', filtrable: false
    },
]);

const colDefRegionCaract = ref([
    {
        prop: 'Region', label: 'Región', minWidth: '120',
        align: 'left', tipo: 'Texto', filtrable: true
    },
    {
        prop: 'TipoDistribucion', label: 'Tipo Distribucion', minWidth: '120',
        align: 'left', tipo: 'lista', filtrable: true
    },
    {
        prop: 'Biblio', label: '', minWidth: '55', align: 'left',
        tipo: 'imagenTexto', filtrable: false
    },
]);

const colDefRegionNomCom = ref([
    {
        prop: 'Region', label: 'Región', minWidth: '120',
        align: 'left', tipo: 'Texto', filtrable: true
    },
    {
        prop: 'Biblio', label: '', minWidth: '55', align: 'left',
        tipo: 'imagenTexto', filtrable: false
    },
]);

const colDefBiblioFinal = ref([
    {
        prop: 'Autor', label: 'Autor', minWidth: '120',
        align: 'left', tipo: 'texto', filtrable: true
    },
    {
        prop: 'Anio', label: 'Año', minWidth: '100',
        align: 'center', tipo: 'texto', filtrable: true
    },
    {
        prop: 'CitaCompleta', label: 'Cita completa', minWidth: '200',
        align: 'left', tipo: 'texto', filtrable: true
    }
]);

const tablaBibliografiasRel = ref([]);
const totalBibliografiasRel = ref(0);

const totalRegNomComun = ref(0);
const totalRegionNomComun = ref(0);

const seleccionarPrimeroPorDefault = () => {
    if (tiposRegionTreeData.value && tiposRegionTreeData.value.length > 0) {
        const primerNodo = tiposRegionTreeData.value[0];
        seleccionarTipoRegionBase(primerNodo);
    }
}

const seleccionarTipoRegionBase = (data) => {
    if (!data) return;
    selectedTipoRegionNode.value = data;
    activePathIds.value = findPathInTree(tiposRegionTreeData.value, data.IdTipoRegion) || [];

    nextTick(() => {
        tiposRegionTreeRef.value?.setCurrentKey(data.IdTipoRegion);
        if (filteredRegionsTree.value && filteredRegionsTree.value.length > 0) {
            const primerNodo = filteredRegionsTree.value[0];
            handleNodeSelected(primerNodo);
        } else {
            selectedNode.value = null;
            if (treeRef.value) treeRef.value.setCurrentKey(null);
        }
    });
};

const findPathInTree = (nodes, targetId, path = []) => {
    for (const node of nodes) {
        const currentPath = [...path, node.IdTipoRegion];
        if (node.IdTipoRegion === targetId) return currentPath;
        if (node.children && node.children.length > 0) {
            const found = findPathInTree(node.children, targetId, currentPath);
            if (found) return found;
        }
    }
    return null;
};

const filteredRegionsTree = computed(() => {
    if (!selectedTipoRegionNode.value) return [];
    const targetTypeId = selectedTipoRegionNode.value.IdTipoRegion;
    const findTypePath = (nodes, targetId) => {
        for (const node of nodes) {
            if (node.IdTipoRegion === targetId) return [node.IdTipoRegion];
            if (node.children && node.children.length > 0) {
                const path = findTypePath(node.children, targetId);
                if (path) return [node.IdTipoRegion, ...path];
            }
        }
        return null;
    };
    const allowedTypeIds = findTypePath(tiposRegionTreeData.value, targetTypeId) || [];
    const filterAndPruneTree = (nodes) => {
        return nodes.reduce((accumulator, node) => {
            const nodeTypeId = node.IdTipoRegion;
            if (allowedTypeIds.includes(nodeTypeId)) {
                const newNode = { ...node, children: [] };
                if (nodeTypeId !== targetTypeId) {
                    if (node.children && node.children.length > 0) {
                        newNode.children = filterAndPruneTree(node.children);
                    }
                    accumulator.push(newNode);
                } else {
                    newNode.children = [];
                    accumulator.push(newNode);
                }
            }
            return accumulator;
        }, []);
    };

    return filterAndPruneTree(JSON.parse(JSON.stringify(localTreeDataReg.value)));
});


const handleManageTiposRegion = () => { esModalTipoRegionVisible.value = true; };
const cerrarModalTipoRegion = () => { cargaRegionesCatalogos(); esModalTipoRegionVisible.value = false; }

const handleNodeSelected = (data) => {
    selectedNode.value = data;
    idRegionSeleccionada.value = data.IdRegion;
    nextTick(() => {
        treeRef.value?.setCurrentKey(data.IdRegion);
    });
};

const getTipoRegionNodeClass = (data) => {
    return activePathIds.value.includes(data.IdTipoRegion) ? 'fila-activa-completa' : '';
};

const handleTipoRegionSelected = (data) => seleccionarTipoRegionBase(data);

const handleTipoRegionExpand = (data) => {
    ultimoEventoExpandTime = Date.now();
    if (!expandedTipoRegionKeys.value.includes(data.IdTipoRegion)) expandedTipoRegionKeys.value.push(data.IdTipoRegion);
    seleccionarTipoRegionBase(data);
};

const handleTipoRegionCollapse = (data) => {
    ultimoEventoExpandTime = Date.now();
    expandedTipoRegionKeys.value = expandedTipoRegionKeys.value.filter(key => key !== data.IdTipoRegion);
    seleccionarTipoRegionBase(data);
};

function findNodeInTipoRegionTree(nodes, id) {
    for (const node of nodes) {
        if (node.IdTipoRegion === id) return node;
        if (node.children) {
            const found = findNodeInTipoRegionTree(node.children, id);
            if (found) return found;
        }
    }
    return null;
}

const cargaRegionesCatalogos = async () => {
    const respRegiones = await axios.get('/carga-regiones');
    if (respRegiones.status === 200) {
        todosLosTiposDeRegion.value = respRegiones.data.treeData;
        todosLosTiposDeRegionTree.value = respRegiones.data.todosLosTiposDeRegion;
        tiposRegionTreeData.value = JSON.parse(JSON.stringify(respRegiones.data.tiposDeRegionTree));
        localTreeDataReg.value = JSON.parse(JSON.stringify(respRegiones.data.treeData));
        treeKey.value++;
    }
}



const onCreaRelacion = async () => {
    const v_idNombre = props.taxonAct.id;
    const v_idNomComun = idNomComunSeleccionado.value;
    const v_idRegion = selectedNode.value ? selectedNode.value.IdRegion : 'NULO';
    const v_idTipoRegion = selectedTipoRegionNode.value ? selectedTipoRegionNode.value.IdTipoRegion : 'NULO';
    if (!v_idNomComun) {
        mostrarNotificacion("Aviso", "Debe seleccionar un nombre común", "warning");
        return;
    }
    if (v_idTipoRegion === 'NULO') {
        mostrarNotificacion("Aviso", "Debe seleccionar un tipo de region", "warning");
        return;
    }
    if (v_idRegion === 'NULO') {
        mostrarNotificacion("Aviso", "Debe seleccionar una región ", "warning");
        return;
    }

    if (Number(v_idTipoRegion) !== Number(selectedNode.value.IdTipoRegion)) {
        mostrarNotificacion("Aviso", "El tipo de región no coincide con la región", "warning");
        return;
    }

    const params = {
        idNombre: v_idNombre,
        idNomComun: v_idNomComun,
        idRegion: v_idRegion,
        idTipoReg: v_idTipoRegion
    };

    try {
        const response = await axios.post(`/alta-relNom-Nomcomun`, params);
        if (response.status === 200) {
            mostrarNotificacion('Ingreso', response.data.message, 'success');
            await recargarNombresAsociados();
        }
    } catch (error) {
        console.error("Error al guardar:", error);
        mostrarNotificacion("Aviso", "La relacion de nombre común con region ya existe", "warning");
    }
};

const recargarNombresAsociados = async () => {
    try {
        const resp = await axios.get(`/cargar-nomcomun-taxon/${props.taxonAct.id}`);
        if (resp.status === 200) {
            nombresAsociadosTaxon.value = resp.data;
            if (typeof tablaNomComunAsociados !== 'undefined') {
                tablaNomComunAsociados.value = resp.data;
            }
        }
    } catch (error) {
        console.error("Error recargando asociados:", error);
    }
};




const cargaNomComunGlobal = async () => {
    const respNomCom = await axios.get('/cargaCatNomComun');
    if (respNomCom.status === 200) {
        tablaNomComun.value = respNomCom.data.data;
        contRegNomCom.value = respNomCom.data.total;
    }
};

watch(
    () => props.taxonAct,
    async (nuevoValor) => {
        idNomComunSeleccionado.value = null;
        nodoSeleccionadoArbol.value = null;
        idRegionSeleccionada.value = null;
        idBiblioSeleccionada.value = null;
        selectedTipoRegionNode.value = null;
        selectedNode.value = null;
        activePathIds.value = [];
        tablaNomComunReg.value = [];
        tablaBibliografiasRel.value = [];
        observacionesGeneral.value = "";
        observaciones.value = "";
        editandoObsGeneral.value = false;
        editandoObs.value = false;
        tablaNomComun.value = [];
        tablaCaracteristicas.value = [];
        if (!nuevoValor?.id) return;
        try {
            const [respNomCom, respCaract, respRegion] = await Promise.allSettled([
                axios.get(`/cargar-nomcomun-taxon/${nuevoValor.id}`),
                axios.get(`/cargaCaracTaxon/${nuevoValor.id}`),
                axios.get(`/cargaRegionesTaxon/${nuevoValor.id}`)
            ]);
            if (respNomCom.status === 'fulfilled' && respNomCom.value.status === 200) {
                const data = respNomCom.value.data;
                tablaNomComun.value = data;
                tablaNomComunAsociados.value = data;
                nombresAsociadosTaxon.value = data;
                totalRegNomComun.value = data.length;
            }
            if (respCaract.status === 'fulfilled' && respCaract.value.status === 200) {
                tablaCaracteristicas.value = respCaract.value.data;
                totalRegCaract.value = respCaract.value.data.length;
            } else {
                console.error("Error al cargar características:", respCaract.reason || "Error 500");
            }

            if (respRegion.status === 'fulfilled' && respRegion.value.status === 200) {
                const data = respRegion.value.data;
                regionesNombre.value = data.regPorNombre;
                totalRegionesNom.value = data.regPorNombre.length;
                regionesCaract.value = data.regPorCaract;
                totalRegionesCaract.value = data.regPorCaract.length;
                regionesNomCom.value = data.regPorNomCom;
                totalRegionesNomCom.value = data.regPorNomCom.length;
            }

        } catch (error) {
            console.error("Error crítico en el watcher:", error);
            if (typeof mostrarNotificacion === 'function') {
                mostrarNotificacion("Error", "No se pudo obtener toda la información del taxón", "error");
            }
        } finally {
            nextTick(() => {
                if (tablaNomComunPrincipalRef.value?.$refs.tableRef) {
                    tablaNomComunPrincipalRef.value.$refs.tableRef.setCurrentRow(null);
                }
                if (refTablaNomComunModal.value?.$refs.tableRef) {
                    refTablaNomComunModal.value.$refs.tableRef.setCurrentRow(null);
                }
                if (refTablaRegionesModal.value?.$refs.tableRef) {
                    refTablaRegionesModal.value.$refs.tableRef.setCurrentRow(null);
                }
                if (refTablaBiblioModal.value?.$refs.tableRef) {
                    refTablaBiblioModal.value.$refs.tableRef.setCurrentRow(null);
                }
                if (tiposRegionTreeRef.value) tiposRegionTreeRef.value.setCurrentKey(null);
                if (treeRef.value) treeRef.value.setCurrentKey(null);
            });
        }
    },
    { immediate: true }
);

const abrirCaract = async () => {
    const respCarac = await axios.get('/cargar-caracteristicas');

    if (respCarac.status === 200) {

        flatTreeDataProp.value = respCarac.data.flatTreeDataProp;
        treeDataProp.value = respCarac.data.treeDataProp;
    }

    dialogFormVisibleCaract.value = true;
}

const cerrarCarac = () => {
    dialogFormVisibleCaract.value = false;
}

const abrirReg = async () => {
    const respRegiones = await axios.get('/carga-regiones');

    if (respRegiones.status === 200) {
        treeRegionDataProp.value = respRegiones.data.treeData;
        tiposDeRegionProp.value = respRegiones.data.todosLosTiposDeRegion;
        tiposDeRegionTreeProp.value = respRegiones.data.tiposDeRegionTree;
    }

    dialogFormVisibleReg.value = true;
}

const clickNomComunOriginal = (row) => {
    if (!row) return;
    idNomComunSeleccionado.value = row.IdNomComun || row.id;
    idRegionSeleccionada.value = null;
    tablaBibliografiasRel.value = [];
    totalBibliografiasRel.value = 0;
    observaciones.value = "";
    tablaNomComunReg.value = row.Regiones || [];
    totalRegionNomComun.value = tablaNomComunReg.value.length;
    nextTick(() => {
        if (refTablaRegionesModal.value?.$refs.tableRef) {
            refTablaRegionesModal.value.$refs.tableRef.setCurrentRow(null);
        }
        if (refTablaBiblioModal.value?.$refs.tableRef) {
            refTablaBiblioModal.value.$refs.tableRef.setCurrentRow(null);
        }
    });
};


const confirmarEliminarAsociacion = () => {
    if (!nodoSeleccionadoArbol.value) {
        mostrarNotificacion("Aviso", "Por favor seleccione un nombre común o una región para eliminar", "warning");
        return;
    }

    const esRegion = !!nodoSeleccionadoArbol.value.Region;
    let mensaje = "";

    if (esRegion) {
        const nombreComunPadre = nombresAsociadosTaxon.value.find(nc =>
            (nc.IdNomComun || nc.id) === nodoSeleccionadoArbol.value.IdNomComun
        );

        if (nombreComunPadre && nombreComunPadre.Regiones && nombreComunPadre.Regiones.length === 1) {
            mensaje = "¿Estás seguro de borrar la región y la asociación del nombre común con el taxón?";
        } else {
            mensaje = "¿Estás seguro de eliminar la región seleccionada y su bibliografía asociada?";
        }
    } else {
        mensaje = `¿Estás seguro de eliminar el nombre común seleccionado y sus regiones y bibliografías?`;
    }

    ElMessageBox({
        title: 'Confirmar eliminación',
        showConfirmButton: false,
        showCancelButton: false,
        customClass: 'message-box-diseno-limpio',
        message: h('div', { class: 'custom-message-content' }, [
            h('div', { class: 'body-content' }, [
                h('div', { class: 'custom-warning-icon-container' }, [
                    h('div', { class: 'custom-warning-circle' }, '!')
                ]),
                h('div', { class: 'text-container' }, [
                    h('p', null, mensaje)
                ])
            ]),
            h('div', { class: 'footer-buttons' }, [
                h(BotonCancelar, { onClick: () => ElMessageBox.close() }),
                h(BotonAceptar, { onClick: ejecutarEliminacion }),
            ])
        ])
    }).catch(() => { });
};

const ejecutarEliminacion = async () => {
    ElMessageBox.close();
    try {
        const payload = {
            idNombre: props.taxonAct.id,
            idNomComun: idNomComunSeleccionado.value,
            idRegion: idRegionSeleccionada.value
        };
        const response = await axios.delete('/eliminar-asociacion-nomcomun', { data: payload });
        if (response.status === 200) {
            mostrarNotificacion("Eliminación", response.data.message, "success");
            await recargarNombresAsociados();
            nodoSeleccionadoArbol.value = null;
            idRegionSeleccionada.value = null;
        }
    } catch (error) {
        console.error("Error al eliminar:", error.response);
        mostrarNotificacion("Error", "No se pudo eliminar la relación.", "error");
    }
};



const clickNomComun = (row) => clickNomComunOriginal(row);
const clickNomCom = (data) => {
    if (!data) return;
    nodoSeleccionadoArbol.value = data;
    tablaBibliografiasRel.value = [];
    totalBibliografiasRel.value = 0;
    observaciones.value = "";
    idBiblioSeleccionada.value = null;
    editandoObs.value = false;
    if (data.NombreComun) {
        idNomComunSeleccionado.value = data.IdNomComun || data.id;
        idRegionSeleccionada.value = null;
        tipoSeleccion.value = 'comun';
        tablaNomComunReg.value = data.Regiones || [];
        if (refTablaRegionesModal.value && refTablaRegionesModal.value.clearCurrentRow) {
            refTablaRegionesModal.value.clearCurrentRow();
        }
    } else {
        idRegionSeleccionada.value = data.IdRegion;
        idNomComunSeleccionado.value = data.IdNomComun;
        tipoSeleccion.value = 'region';
    }
    observacionesGeneral.value = data.Observaciones || "";
    valorOriginalObsGeneral.value = data.Observaciones || "";
};


const clickCaract = (row) => {
    idCaractSeleccionada.value = row.IdCatNombre || row.id;
    tablaCaractReg.value = row.Regiones || [];
    totalRegionCaract.value = (row.Regiones || []).length;
    idRegionCaractSeleccionada.value = null;
    idTipoDistSeleccionada.value = null;
    actObsCaract.value = true;
    rowCaract.value = row;
    observacionesCaractGral.value = row.Observaciones


    cargarBibliografiasRelCaractSolo();
}

const clickRegCaract = async (row) => {
    rowCaractReg.value = row;
    obsCaractReg.value = row.Observaciones;

    idRegionCaractSeleccionada.value = row.IdRegion;
    if (row.TipDistribucion && typeof row.TipDistribucion === 'object') {
        idTipoDistSeleccionada.value = row.TipDistribucion.IdTipoDistribucion || row.TipDistribucion.id;
    } else {
        idTipoDistSeleccionada.value = row.IdTipoDistribucion;
    }
    if (idCaractSeleccionada.value && idRegionCaractSeleccionada.value && idTipoDistSeleccionada.value) {
        await cargarBibliografiasRelCaract();
    } else {
        console.error("Todavía falta algún ID:", {
            caract: idCaractSeleccionada.value,
            reg: idRegionCaractSeleccionada.value,
            tipoDist: idTipoDistSeleccionada.value
        });
    }
};

const editarCarct = () => {
    actObsCaract.value = false;
}

const guardarCaract = async () => {
    const params = {
        idNombre: props.taxonAct.id,
        idCatNombre: rowCaract.value.IdCatNombre,
        observaciones: observacionesCaractGral.value,
    };

    try {
        const response = await axios.put(`/actualiza-Caract-Taxon`, params);

        if (response.status === 200) {
            mostrarNotificacion('Aviso', response.data.message, 'success');
            rowCaract.value.Observaciones = observacionesCaractGral.value;
            actObsCaract.value = true;

        }
    } catch (error) {
        if (error.response.status === 422) {
            const errorMessages = Object.values(error.response.data.errors).flat();
            errorMessages.forEach(msg => {
                mostrarNotificacionError(
                    "Error",
                    msg,
                    "Error",
                    5000
                );
            });
        }
    }
}

const editarCaractReg = () => {
    actObsCaractReg.value = false;
    tipDistAct.value = rowCaractReg.value.TipDistribucion.id;
}

const actValorLista = (idAct) => {
    idTipoDistMod.value = idAct;
}

const guardarCaractReg = async () => {
    let idTipDist;
    let response;

    if (idTipoDistMod.value != 0) {
        idTipDist = idTipoDistMod.value;
    } else {
        idTipDist = tipDistAct.value;
    }

    const params = {
        idNombre: props.taxonAct.id,
        idCatNombre: rowCaract.value.IdCatNombre,
        idRegion: rowCaractReg.value.IdRegion,
        idTipoDistAct: tipDistAct.value,
        idTipoDistNue: idTipDist,
        observaciones: obsCaractReg.value,
    };

    try {
        response = await axios.put(`/actualiza-Caract-Taxon-Reg`, params);

        if (response.status === 200) {
            mostrarNotificacion('Aviso', response.data.message, 'success');
            rowCaractReg.value.Observaciones = obsCaractReg.value;
            rowCaractReg.value.TipDistribucion.id = idTipDist
            actObsCaractReg.value = true;
        }
    } catch (error) {
        if (error.response.status === 422) {
            const errorMessages = Object.values(error.response.data.errors).flat();
            errorMessages.forEach(msg => {
                mostrarNotificacionError(
                    "Error",
                    msg,
                    "Error",
                    5000
                );
            });
        }
    }
}

const eliminarCaract = async (row) => {
    const procederConEliminacion = async () => {
        try {
            ElMessageBox.close();

            await axios.delete(`/eliminar-Caract-Taxon`, {
                params: {
                    idNombre: props.taxonAct.id,
                    idCatNombre: rowCaract.value.IdCatNombre,
                }
            });

            const index = tablaCaracteristicas.value.indexOf(row);

            if (index !== -1) {
                tablaCaracteristicas.value.splice(index, 1);
                tablaCaractReg.value = null;
            }

            mostrarNotificacion('Eliminación', `La autoridad taxonómica ha sido eliminada correctamente.`, 'success');
        } catch (apiError) {
            mostrarNotificacionError('Aviso', `El autor ${nombreAutorEliminado} no se puede eliminar. Este autor esta asociado a un taxón.`, 'warning');
        }
    };

    const cancelarEliminacion = () => {
        ElMessageBox.close();
    };

    const mensaje = `¿Está seguro de eliminar la característica "${row.Caracteristica}" con sus regiones y su bibliografía relacionada? Esta acción no se puede revertir.`;

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
}

const eliminarCaractReg = async (row) => {
    const procederConEliminacion = async () => {
        try {
            ElMessageBox.close();

            await axios.delete(`/eliminar-Caract-Taxon-Reg`, {
                params: {
                    idNombre: props.taxonAct.id,
                    idCatNombre: rowCaract.value.IdCatNombre,
                    idRegion: row.IdRegion,
                    idTipoDist: row.TipDistribucion.id
                }
            });

            const index = tablaCaractReg.value.indexOf(row);

            if (index !== -1) {
                tablaCaractReg.value.splice(index, 1);
            }

            mostrarNotificacion('Eliminación', `La autoridad taxonómica ha sido eliminada correctamente.`, 'success');
        } catch (apiError) {
            mostrarNotificacionError('Aviso', `El autor ${nombreAutorEliminado} no se puede eliminar. Este autor esta asociado a un taxón.`, 'warning');
        }
    };
    const cancelarEliminacion = () => {
        ElMessageBox.close();
    };

    const mensaje = `¿Está seguro de eliminar la región "${row.Region}" y su bibliografía relacionada? Esta acción no se puede revertir.`;

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
}

const clickRegNomCom = async (row) => {
    editandoObsReg.value = false;
    idRegionSeleccionada.value = row.IdRegion || row.id || row.IdCatRegion;
    idBiblioSeleccionada.value = null;
    nextTick(() => {
        if (refTablaBiblioModal.value?.$refs.tableRef) {
            refTablaBiblioModal.value.$refs.tableRef.setCurrentRow(null);
        }
    });
    observacionesRegTab.value = row.ObservacionesReg || row.Observaciones || "";
    await cargarBibliografiasRelacionadas();
};


const cargarBibliografiasRelCaract = async () => {
    try {
        const response = await axios.get('/obtener-biblio-caract-region', {
            params: {
                IdNombre: props.taxonAct.id,
                IdCatNombre: idCaractSeleccionada.value,
                IdRegion: idRegionCaractSeleccionada.value,
                IdTipoDistribucion: idTipoDistSeleccionada.value
            }
        });

        if (response.status === 200) {
            tablaBibliografiasRel.value = response.data;
            totalBibliografiasRel.value = response.data.length;
        }
    } catch (error) {
        console.error("Error en la petición:", error);
    }
};

const cargarBibliografiasRelacionadas = async () => {
    if (idNomComunSeleccionado.value && idRegionSeleccionada.value && props.taxonAct.id) {
        try {
            const url = `/obtener-biblio-nomcomun-region/${idNomComunSeleccionado.value}/${idRegionSeleccionada.value}/${props.taxonAct.id}`;
            const response = await axios.get(url);

            tablaBibliografiasRel.value = response.data;
            totalBibliografiasRel.value = response.data.length;

            if (response.data.length > 0) {
                const primerRegistro = response.data[0];
                observaciones.value = primerRegistro.Observaciones || primerRegistro.observaciones || "";
            } else {
                observaciones.value = "";
            }
        } catch (error) {
            console.error("Error al traer datos:", error);
            tablaBibliografiasRel.value = [];
            observaciones.value = "";
        }
    }
}

const cerrarReg = () => {
    dialogFormVisibleReg.value = false;
}

const abrirNomCom = () => {
    dialogFormVisibleNomCom.value = true;
}

const cerrarNomCom = () => {
    dialogFormVisibleNomCom.value = false;
}

const cerrarRelNomCom = () => {
    dialogFormVisibleRelNomCom.value = false;
}

const cerrarRelCaract = () => {
    dialogFormVisibleRelCaract.value = false;
}

const nuevoRelNomComun = () => {
    dialogFormVisibleRelNomCom.value = true;
}

const nuevoRelCaract = () => {
    dialogFormVisibleRelCaract.value = true;
}

const Guardar = () => {

}

onMounted(async () => {
    cargaRegionesCatalogos();
    cargaNomComunGlobal();

    const respCarac = await axios.get('/cargar-caracteristicas');
    if (respCarac.status === 200) {
        flatTreeDataProp.value = respCarac.data.flatTreeDataProp;
        treeDataProp.value = respCarac.data.treeDataProp;
    }

    const tiposDistrib = await axios.get('/carga-tipos-distribucion');
    if (tiposDistrib.status === 200) tiposDistribucion.value = tiposDistrib.data;

});



const filterText = ref('');

const buscadorDeshabilitado = computed(() => {
    return !selectedTipoRegionNode.value;
});

const irAlNodoBuscado = () => {
    if (!filterText.value || !treeRef.value) return;

    const textoBusqueda = filterText.value.toLowerCase();
    const idTipoTarget = selectedTipoRegionNode.value?.IdTipoRegion;

    const encontrarEnArbol = (nodos) => {
        for (const nodo of nodos) {
            const coincideNombre = (nodo.NombreRegion || "").toLowerCase().includes(textoBusqueda);
            const coincideTipo = nodo.IdTipoRegion === idTipoTarget;

            if (coincideNombre && coincideTipo) return nodo;

            if (nodo.children?.length) {
                const encontrado = encontrarEnArbol(nodo.children);
                if (encontrado) return encontrado;
            }
        }
        return null;
    };

    const match = encontrarEnArbol(filteredRegionsTree.value);

    if (match) {
        selectedNode.value = match;
        treeRef.value.setCurrentKey(match.IdRegion);

        let nodeInTree = treeRef.value.getNode(match.IdRegion);
        if (nodeInTree) {
            let parent = nodeInTree.parent;
            while (parent) {
                parent.expanded = true;
                parent = parent.parent;
            }
        }

        nextTick(() => {
            const el = document.getElementById('region-node-' + match.IdRegion);
            if (el) el.scrollIntoView({ behavior: 'smooth', block: 'center' });
        });
    } else {
        console.log("No se encontró la región");
    }
};

watch(filterText, (nuevoValor) => {
    if (nuevoValor) {
        filterText.value = nuevoValor.toUpperCase();
    }
});
</script>

<style scoped>
.demo-tree {
    flex: 1;
    overflow-y: auto;
    overflow-x: hidden;
    height: 100%;
    display: block;
}

:deep(.el-tree-node__content) {
    height: auto !important;
    min-height: 26px;
    align-items: flex-start;
    padding: 4px 0;
}

.nodo-texto,
.nodo-tipo-region {
    white-space: normal !important;
    word-break: break-all;
    font-size: 13px;
    line-height: 1.3;
    display: inline-block;
    width: 90%;
    margin-left: 5px;
}

.container-arbol {
    height: 100%;
    display: flex;
    flex-direction: column;
    border: none !important;
}

:deep(.container-arbol .el-card__body) {
    flex: 1;
    overflow: auto;
    padding: 10px;
}

.tree-card {
    width: 0%;
    max-width: 100px;
    max-height: 590px;
    display: flex;
    flex-direction: column;
}

.table-wrapper :deep(.el-table__body tr.current-row > td) {
    background-color: #ddf6dd !important;
    color: #0d6efd !important;
    font-weight: bold;
}

.table-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 1px 16px;
    background: #d9e1eb;
    border-bottom: 1px solid #f5ebeb;
}

.table-title {
    font-size: 14px;
    font-weight: 600;
    color: #303133;
}

.demo-panel {
    width: 100%;
    height: 100%;
}

.tree-full {
    width: 100%;
    height: 100%;
    overflow: auto;
}

.panel-nombre {
    background: #fff;
    width: 100%;
    height: 100%;
    overflow: auto;
}

.panel-carac {
    background: rgb(189, 231, 241);
}

.panel-nomcomun {
    background: rgb(236, 241, 189);
}

.origen-rojo {
    color: #d32f2f;
    font-weight: bold;
}

.origen-verde {
    color: #0556cf;
    font-weight: bold;
}

.origen-amarillo {
    color: #b8860b;
    font-weight: bold;
}

.table-wrapper :deep(.el-table__row.current-row)>td {
    background-color: #ddf6dd !important;
}

.table-wrapper :deep(.el-table__row.current-row) .cell {

    color: #007bff !important;
    font-weight: bold;
}

.table-wrapper :deep(.el-table__body tr:hover > td) {
    background-color: #ddf6dd !important;
}

.table-wrapper :deep(.el-table__row:not(.current-row)) {
    background-color: transparent !important;

}

.box-card {
    display: flex;
    flex-direction: column;
    transition: all 0.3s ease;
}

.el-main {
    padding: 20px !important;
    overflow-y: auto !important;
}

.el-aside {
    transition: width 0.3s ease;
}

.el-tabs {
    width: 100%;
}

.fila-activa-completa {
    color: #007bff !important;
    font-weight: bold !important;

}

.is-active-path {
    color: #007bff !important;
    font-weight: bold !important;
}

:deep(.el-tree-node__content:has(.is-active-path)) {
    background-color: #ddf6dd !important;
    border-radius: 4px;
    margin: 1px 0;
}

:deep(.el-tree-node.is-current > .el-tree-node__content) {
    background-color: #c9eec9 !important;
}

.nodo-tipo-region {
    font-size: 13px;
    padding: 2px 0;
    display: block;
    width: 100%;
}



.list-panel {
    height: 100%;
    display: flex;
    flex-direction: column;
}

.list-panel :deep(.el-card__header) {
    position: sticky;
    top: 0;
    z-index: 10;
    background-color: white;
    padding: 10px 15px;
    border-bottom: 1px solid #ddd;
}

.list-panel :deep(.el-card__body) {
    flex: 1;
    overflow: hidden;
    display: flex;
    flex-direction: column;
    padding: 0;
}

.table-wrapper :deep(.el-table__body tr.current-row > td) {
    background-color: #ddf6dd !important;
    color: #0d6efd !important;
    font-weight: bold !important;
}

.table-wrapper :deep(.el-table__body tr.current-row:hover > td) {
    background-color: #c9eec9 !important;
}
</style>
