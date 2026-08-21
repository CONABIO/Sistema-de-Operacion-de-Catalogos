    <template>
        <div>
            <el-card class="box-card" style="width: 1540px; margin: 20px auto; border-radius: 12px; height: 820px;">
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
                                <el-col :span="5" style="display: flex; justify-content: flex-end;">
                                    <div style="display: flex; gap: 5px;">
                                        <BotonCaract @click="abrirCaract" style="flex-shrink: 0;" />
                                        <BotonRegiones @click="abrirReg" style="flex-shrink: 0;" />
                                        <BotonNomComun @click="abrirNomCom" style="flex-shrink: 0;" />
                                        <BotonSalir accion="cerrar" @salir="closeDialog" style="flex-shrink: 0;" />
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
                                    <el-container class="contenedor-rel-caract">
                                        <RelCaract :taxonActual=props.taxonAct @cerrar="cerrarRelCaract" />
                                    </el-container>
                                </el-tab-pane>
                                <el-tab-pane label="Regiones" name="Region">
                                    <div style="height: 540px; box-shadow: var(--el-border-color-light) 0px 0px 10px">
                                        <div style="padding: 10px 15px; border-bottom: 1px solid #eee; display: flex; align-items: center; gap: 10px;">
                                            <span style="font-weight: bold; color: #606266; font-size: 14px;">Búsqueda general:</span>
                                            <SwitchBusqueda v-model="filtroRegionesGeneral" />
                                        </div>
                                        <el-splitter lazy>
                                            <el-splitter-panel :size="'23%'">
                                                <div class="demo-panel panel-nomcomun table-wrapper">
                                                    <el-container>
                                                        <el-header height="40px"
                                                            style=" display:flex; justify-content:center; align-items:center;">
                                                            <span style="font-size: 15px; font-weight: bold;">
                                                                Regiones asociadas al
                                                                <br>
                                                                Taxón-Nombre Comun
                                                            </span>
                                                        </el-header>
                                                         <TablaFiltrable
                                                            :columnas="colDefRegionNomCom"
                                                            :datos="regionesNomComFull"
                                                            :totalItems="totalRegionesNomComFull"
                                                            :tipo-busqueda-externo="filtroRegionesGeneral"
                                                            :mostrar-switch-local="false"
                                                            :itemsPerPage="8"
                                                            :mostrarBiblio="false"
                                                            :mostrarAcci="false"
                                                            :alturaTabla="330"
                                                            :highlight-current-row="true"
                                                            :mostrarNuevo="false"
                                                            :mostrarEditar="false"
                                                            :mostrarBorrar="false"
                                                            :mostrarSalir="false">
                                                        </TablaFiltrable>
                                                    </el-container>
                                                </div>
                                            </el-splitter-panel>
                                            <el-splitter-panel :size="'25%'">
                                                <div class="demo-panel panel-carac table-wrapper">
                                                    <el-container>
                                                        <el-header height="40px"
                                                            style=" display:flex; justify-content:center; align-items:center;">
                                                            <span
                                                                style="font-size: 15px;  font-weight: bold;">
                                                                Regiones asociadas al
                                                                <br>
                                                                Taxón-Caracteristica
                                                            </span>
                                                        </el-header>
                                                        <TablaFiltrable :columnas="colDefRegionCaract"
                                                            :datos="regionesCaract"
                                                            :totalItems="totalRegionesCaract"
                                                            :tipo-busqueda-externo="filtroRegionesGeneral"
                                                            :mostrar-switch-local="false"
                                                            :valoresOpcion="tiposDistribucion"
                                                            :habOpciones="habOpciones" :itemsPerPage=8
                                                            :mostrarBiblio="false" :mostrarAcci="false" :alturaTabla=330
                                                            :highlight-current-row="true" :mostrarNuevo="false"
                                                            :mostrarGuardar="false" :mostrarEditar="false"
                                                            :mostrarBorrar="false" :mostrarSalir="false">
                                                        </TablaFiltrable>
                                                    </el-container>
                                                </div>
                                            </el-splitter-panel>
                                            <el-splitter-panel min="50">
                                                <div class="demo-panel panel-nombre table-wrapper" style="height: 100%;">
                                                    <el-splitter layout="vertical" style="height: 94%;">
                                                        <el-splitter-panel :size="'125%'">
                                                            <el-container style="width:100%; height:100%;">
                                                                <el-header height="40px" style="display:flex; justify-content:center; align-items:center;">
                                                                    <span style="font-size: 15px; font-weight: bold;">
                                                                        Regiones asociadas al Taxón
                                                                    </span>
                                                                </el-header>

                                                                <TablaFiltrable
                                                                    ref="refTablaRegionesNombre"
                                                                    :columnas="colDefRegionNombre"
                                                                    :datos="regionesNombre" :row-key="'IdRegion'"
                                                                    :totalItems="totalRegionesNom"  @row-click="handleRowClickRegion"
                                                                    @editar-item="habilitarEdicionRegion"
                                                                    :deshabilitarGuardar="!editandoFilaRegion"
                                                                    :valoresOpcion="tiposDistribucion"
                                                                    :tipo-busqueda-externo="filtroRegionesGeneral"
                                                                    :mostrar-switch-local="false"
                                                                    :habOpciones="habOpciones" :itemsPerPage=5
                                                                    :mostrarBiblio="true" :mostrarAcci="false" :alturaTabla="240"
                                                                    :highlight-current-row="true" :mostrarNuevo="true" 
                                                                    :mostrarTipoDist="true"  @abrir-tipo-dist="abrirCatalogoTipoDist" 
                                                                    :mostrarRegion="false" :mostrarGuardar="true" @eliminar-item="eliminarRegNombre"
                                                                    :mostrarEditar="true" :mostrarBorrar="true"
                                                                    :mostrarSalir="false" @nuevo-item="abrirReg" @guardar="guardarRegNombre"  @abrir-biblio="abrirResumenRegionTaxon">
                                                                </TablaFiltrable>
                                                            </el-container>
                                                        </el-splitter-panel>

                                                        <el-splitter-panel :size="'48%'">
                                                            <el-card class="panel-card list-panel" shadow="never" style="border:none;">
                                                                <template #header>
                                                                    <div style="display: flex; align-items: center; margin-top: -5px; margin-bottom: -6px;">
                                                                        <span style="font-weight: bold; font-size: 14px;">Observaciones Región - Taxón</span>
                                                                    </div>
                                                                </template>
                                                                <div style="padding: 10px;">
                                                                    <el-input 
                                                                        v-model="obsRegionTaxon" 
                                                                        type="textarea" 
                                                                        :rows="3" 
                                                                        placeholder="Escriba las observaciones de la relación..." 
                                                                        :disabled="!editandoFilaRegion"
                                                                    />
                                                                </div>
                                                            </el-card>
                                                        </el-splitter-panel>

                                                    </el-splitter>
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
                <CuerpoRegion :modal="true" @cerrar="cerrarReg" :treeDataProp="treeRegionDataProp" @traspasar="ejecutarTraspasoDesdeModal"
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

            <DialogForm v-model="dialogResumenRegionesVisible" :botCerrar="true" :pressEsc="false" :width="'85%'">
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
            
            <DialogForm v-model="dialogResumenRegionTaxonVisible" :botCerrar="true" :pressEsc="false" :width="'80%'">
    <div style="height: 750px; background-color: #fff; display: flex; flex-direction: column; gap: 15px;">
        <el-header class="header">
            <div class="header-content">
                <h1 class="titulo">Asociación de Región - Taxón - Bibliografía</h1>
            </div>
        </el-header>

        <div style="display: flex; justify-content: space-between; align-items: center; padding: 0 10px;">
            <span style="font-size: 18px; color: #8A2815; font-weight: bold;">
                {{ props.taxonAct.label }}
            </span>
            <BotonSalir accion="cerrar" @salir="dialogResumenRegionTaxonVisible = false" />
        </div>

        <div style="flex: 1; min-height: 0;">
            <el-splitter style="height: 100%; border: 1px solid #ddd; border-radius: 8px;">
                <el-splitter-panel :size="'40%'">
                    <div class="table-wrapper" style="height: 100%; display: flex; flex-direction: column; border-right: 1px solid #eee;">
                        <div style="background-color: #bae1f2; padding: 10px; text-align: center; border-bottom: 1px solid #ddd;">
                            <span style="color: #4b7a94; font-weight: bold;">Regiones Asociadas</span>
                        </div>
                        <div style="flex: 1; padding: 10px; overflow: auto;">
                            <TablaFiltrable 
                               ref="refTablaRegionesModalResumen"
                                :columnas="colDefRegionNombre" 
                                :datos="regionesNombre" 
                                :totalItems="totalRegionesNom"
                                :alturaTabla="460" 
                                :itemsPerPage=10
                                :highlight-current-row="true"
                                :mostrarBiblio="false" 
                                :mostrarAcci="false"
                                :mostrarNuevo="false"
                                :mostrarEditar="false"
                                :mostrarBorrar="false"
                                :mostrarSalir="false"
                                @row-click="clickRegResumenTaxon" 
                            />
                        </div>
                    </div>
                </el-splitter-panel>

                <el-splitter-panel>
                    <el-splitter layout="vertical">
                        <el-splitter-panel :size="'70%'">
                            <div class="table-wrapper" style="height: 100%; display: flex; flex-direction: column;">
                                <div style="background-color: #f4f4bf; padding: 10px; text-align: center; border-bottom: 1px solid #ddd;">
                                    <span style="color: #856404; font-weight: bold;">Bibliografía</span>
                                </div>
                                <div style="flex: 1; padding: 10px; overflow: auto;">
                                    <TablaFiltrable 
                                        ref="refTablaBiblioRegResumen"
                                        :columnas="colDefBiblioFinal" 
                                        :datos="tablaBibliografiasRel"
                                        :totalItems="totalBibliografiasRel" 
                                        :alturaTabla="270"
                                        :highlight-current-row="true" 
                                        :mostrarNuevo="true"
                                        :mostrarEditar="true" 
                                        :mostrarBorrar="true"
                                        :mostrarSalir="false"
                                        :mostrar-biblio="false"
                                        :mostrar-guardar="true"
                                        @row-click="clickBiblioRel"
                                        @guardar="guardarCambiosObs" 
                                        @nuevo-item="abrirBiblio"
                                        @eliminar-item="eliminarBiblioRel" 
                                        @abrir-biblio="abrirBiblio"
                                        :deshabilitarGuardar="!editandoObs" 
                                        @editar-item="habilitarEdicionObs"
                                    />
                                </div>
                            </div>
                        </el-splitter-panel>

                        <el-splitter-panel :size="'30%'">
                            <div style="padding: 15px; background: #fff; border-top: 2px solid #eee; height: 100%; overflow: auto;">
                                <p style="font-size: 13px; color: #333; margin-bottom: 8px; font-weight: bold;">
                                    Cita completa
                                </p>
                                <div style="margin-bottom: 15px; padding: 10px; background-color: #f8f9fa; border: 1px solid #e4e7ed; border-radius: 4px; font-size: 12px; color: #606266; line-height: 1.4;">
                                    {{ citaCompletaResumen || '' }}
                                </div>

                                <p style="font-size: 13px; color: #333; margin-bottom: 8px; font-weight: bold;">
                                    Observaciones Región - Bibliografía
                                </p>
                                <el-input v-model="observaciones" type="textarea" :rows="3" placeholder="Observaciones..." :disabled="!editandoObs"  />
                            </div>
                        </el-splitter-panel>
                    </el-splitter>
                </el-splitter-panel>
            </el-splitter>
        </div>
    </div>
</DialogForm>


<DialogForm v-model="dialogFormVisibleTipoDist" :botCerrar="true" :pressEsc="false" :width="'75%'">
    <CuerpoTipoDistribucion :modal="true" @cerrar="cerrarTipoDist" />
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
import CuerpoTipoDistribucion from '@/Pages/Socat/TiposDistribucion/CuerpoTipoDistribucion.vue';
import SwitchBusqueda from '@/Components/Biotica/SwitchBusqueda.vue';
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
import { onMounted,onUnmounted, ref, watch, computed, h, nextTick } from 'vue';
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


const observacionesGeneral = ref("");

let backupFilaRegion = null;

const cancelarEdicionRegion = () => {
    if (!editandoFilaRegion.value) return;
    if (backupFilaRegion) {
        const index = regionesNombre.value.findIndex(r => r.IdRegion === backupFilaRegion.IdRegion);
        if (index !== -1) {
            regionesNombre.value[index] = JSON.parse(JSON.stringify(backupFilaRegion));
        }
    }
    editandoFilaRegion.value = false;
    filaRegionSeleccionada.value = null;
    backupFilaRegion = null;
    obsRegionTaxon.value = '';
    if (refTablaRegionesNombre.value) {
        refTablaRegionesNombre.value.clearCurrentRow();
    }
};


const handleKeyDown = (event) => {
    if (event.key === 'Escape' || event.keyCode === 27) {
        if (editandoFilaRegion.value) {
            event.preventDefault();
            event.stopPropagation(); 
            cancelarEdicionRegion();
        }
    }
};


const habilitarEdicionRegion = (row) => {
    if (editandoFilaRegion.value) {
        mostrarNotificacion(
            "Aviso",
            "Ya existe una edición en curso. Guarde o cancele antes de editar otro registro.",
            "warning"
        );
        return; 
    }
    backupFilaRegion = JSON.parse(JSON.stringify(row));
    filaRegionSeleccionada.value = row;
    editandoFilaRegion.value = true;
    obsRegionTaxon.value = row.Observaciones || '';
};


const eliminarRegNombre = async (row) => {
    const procederConEliminacion = async () => {
        try {
            ElMessageBox.close();
            await axios.delete('/eliminar-region-taxon', {
                data: {
                    IdNombre: props.taxonAct.id,
                    IdRegion: row.IdRegion
                }
            });
            const index = regionesNombre.value.findIndex(r => r.IdRegion === row.IdRegion);
            if (index !== -1) {
                regionesNombre.value.splice(index, 1);
                totalRegionesNom.value = regionesNombre.value.length;
            }
            mostrarNotificacion('Eliminación', 'La región y su bibliografía han sido eliminadas correctamente.', 'success');
        } catch (error) {
            console.error(error);
            mostrarNotificacion('Error', 'No se pudo eliminar la asociación.', 'error');
        }
    };

    const mensaje = `¿Está seguro de eliminar la región seleccionada y toda su bibliografía asociada? Esta acción no se puede revertir.`;

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
                h(BotonAceptar, { onClick: procederConEliminacion }),
            ])
        ])
    }).catch(() => { });
};

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
        mostrarNotificacion("Aviso", "Seleccione una bibliografía de la tabla primero", "warning");
        return;
    }

    const procederConGuardado = async () => {
        ElMessageBox.close();
        try {
            let endpoint = '';
            let payload = {
                IdNombre: props.taxonAct.id,
                IdRegion: idRegionSeleccionada.value,
                IdBibliografia: idBiblioSeleccionada.value,
                Observaciones: observaciones.value
            };
            if (idNomComunSeleccionado.value) {
                endpoint = '/actualizar-obs-nomcomun-region';
                payload.IdNomComun = idNomComunSeleccionado.value;
            } 
            else {
                const regionRow = regionesNombre.value.find(r => r.IdRegion === idRegionSeleccionada.value);
                const idTipoDist = regionRow?.TipoDistribucion?.id || regionRow?.IdTipoDistribucion;

                if (!idTipoDist) {
                    mostrarNotificacion("Error", "No se pudo determinar el tipo de distribución de la región.", "error");
                    return;
                }
                endpoint = '/asociar-biblio-region-taxon'; 
                payload.IdTipoDistribucion = idTipoDist;
            }
            const response = await axios.post(endpoint, payload);
            if (response.status === 200) {
                mostrarNotificacion("Éxito", "La observación se guardó correctamente", "success");
                editandoObs.value = false; 
                const index = tablaBibliografiasRel.value.findIndex(
                    b => (b.IdBibliografia || b.id) === idBiblioSeleccionada.value
                );
                if (index !== -1) {
                    tablaBibliografiasRel.value[index].Observaciones = observaciones.value;
                }
            }
        } catch (error) {
            console.error("ERROR AL GUARDAR:", error.response?.data);
            mostrarNotificacion("Error", "No se pudo guardar. Revisa la consola para más detalles.", "error");
        }
    };

    ElMessageBox({
        title: 'Confirmar guardado',
        showConfirmButton: false,
        showCancelButton: false,
        customClass: 'message-box-diseno-limpio',
        message: h('div', { class: 'custom-message-content' }, [
            h('div', { class: 'body-content' }, [
                h('div', { class: 'custom-warning-icon-container' }, [h('div', { class: 'custom-warning-circle' }, '!')]),
                h('div', { class: 'text-container' }, [
                    h('p', null, "¿Deseas guardar los cambios realizados en las observaciones de región - bibliografía?")
                ])
            ]),
            h('div', { class: 'footer-buttons' }, [
                h(BotonCancelar, { onClick: () => ElMessageBox.close() }),
                h(BotonAceptar, { onClick: procederConGuardado }),
            ])
        ])
    }).catch(() => {});
};

const clickBiblioRel = (row) => {
    if (!row) return;
    const idSugerido = String(row.IdBibliografia || row.id || row.IdCatBiblio || '');
    const idActual = String(idBiblioSeleccionada.value || '');
    if (editandoObs.value) {
        if (idActual !== idSugerido) {
            mostrarNotificacion(
                "Aviso", 
                "Tiene una edición en curso, por favos guarda los datos antes de seleccionar otro registro.", 
                "warning"
            );
            nextTick(() => {
                if (refTablaBiblioRegResumen.value) {
                    const filaOriginal = tablaBibliografiasRel.value.find(b => 
                        String(b.IdBibliografia || b.id || b.IdCatBiblio || '') === idActual
                    );
                    if (filaOriginal) {
                        refTablaBiblioRegResumen.value.setCurrentRow(filaOriginal);
                    }
                }
            });
            return; 
        }
    }
    idBiblioSeleccionada.value = idSugerido;
    observaciones.value = row.Observaciones || row.observaciones || "";
    citaCompletaResumen.value = row.CitaCompleta || row.citaCompleta || "";
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
    } else if (tabInicial.value === 'Region') {
        const row = regionesNombre.value.find(r => r.IdRegion === idRegionSeleccionada.value);
        if (!row) {
            mostrarNotificacion("Aviso", "Por favor, selecciona una región de la lista izquierda primero.", "warning");
            return;
        }
        const idTipoDist = row.TipoDistribucion?.id || row.IdTipoDistribucion;
        
        if (!idTipoDist) {
            mostrarNotificacion("Error", "La región seleccionada debe tener un tipo de distribución guardado.", "error");
            return;
        }

        const params = {
            IdNombre: props.taxonAct.id,
            IdRegion: row.IdRegion,
            IdTipoDistribucion: idTipoDist,
            IdBibliografia: idBiblio
        };

        try {
            const response = await axios.post('/asociar-biblio-region-taxon', params);
            if (response.status === 200) {
                if (row.Biblio) row.Biblio.url = '/storage/images/Libro_Verde.svg';
                await clickRegResumenTaxon(row);
            }
        } catch (error) {
            console.error(error);
            mostrarNotificacion("Error", "No se pudo realizar la asociación.", "error");
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
    else if (tabInicial.value === 'Region') {
        if (!idRegionSeleccionada.value) {
            mostrarNotificacion("Aviso", "Por favor selecciona una región de la lista izquierda primero.", "warning");
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


const regionesNomComFull = computed(() => {
    const lista = [];
    nombresAsociadosTaxon.value.forEach(nc => {
        if (nc.Regiones && Array.isArray(nc.Regiones)) {
            nc.Regiones.forEach(reg => {
                lista.push({
                    ...reg,
                    NombreComunPadre: nc.NombreComun
                });
            });
        }
    });
    return lista;
});

const ejecutarTraspasoDesdeModal = (nodoRegion) => {
    tabInicial.value = "Region";
    const existe = regionesNombre.value.find(r => r.IdRegion === nodoRegion.IdRegion);
    if (!existe) {
        const nuevaFila = {
            IdRegion: nodoRegion.IdRegion,
            Region: nodoRegion.NombreRegion,
            TipoDistribucion: { id: null }, 
            Observaciones: "",
            Biblio: { url: '/storage/images/Libro_Rojo.svg', texto: '' },
            esNuevo: true
        };
        regionesNombre.value.unshift(nuevaFila);
        totalRegionesNom.value = regionesNombre.value.length;
        editandoFilaRegion.value = true;
        filaRegionSeleccionada.value = nuevaFila; 
        mostrarNotificacion(
            "Ingreso", 
            "La region ha sido asociada, por favor seleccione un tipo de distribucion y guarde.", 
            "success"
        );
        nextTick(() => {
            if (refTablaRegionesNombre.value) {
                refTablaRegionesNombre.value.irAPagina(1);
                refTablaRegionesNombre.value.setCurrentRow(nuevaFila);
                if (typeof refTablaRegionesNombre.value.activarEdicionDesdePadre === 'function') {
                    refTablaRegionesNombre.value.activarEdicionDesdePadre(nuevaFila);
                }
            }
        });
    } else {
        mostrarNotificacion("Aviso", "Esta región ya se encuentra asociada al taxón.", "warning");
    }
    
    cerrarReg(); 
};

const guardarRegNombre = async (modo) => {
    const row = filaRegionSeleccionada.value;
    if (!row) {
        mostrarNotificacion("Aviso", "Por favor, seleccione la fila que desea guardar.", "warning");
        return;
    }
    const idTipoDist = row.TipoDistribucion?.id;
    if (!idTipoDist) {
        mostrarNotificacion("Error", "Debe seleccionar un Tipo de Distribución.", "error");
        return;
    }

    const params = {
        IdNombre: props.taxonAct.id,
        IdRegion: row.IdRegion,
        IdTipoDistribucion: idTipoDist,
        Observaciones: obsRegionTaxon.value 
    };

    try {
        const response = await axios.post('/alta-relNombre-Region', params);
        if (response.status === 200) {
            mostrarNotificacion("Modificación", "El tipo de distribución y la observacion de la region seleccionada han sido modificada", "success");
            
            row.Observaciones = obsRegionTaxon.value;
            
            row.esNuevo = false;
            editandoFilaRegion.value = false;
            filaRegionSeleccionada.value = null;
            if (refTablaRegionesNombre.value) {
                refTablaRegionesNombre.value.clearCurrentRow(); 
            }
        }
    } catch (error) {
        console.error(error);
        mostrarNotificacion("Error", "No se pudo guardar la modificación.", "error");
    }
};

const handleRowClickRegion = (row) => {
    if (!row) return;

    if (editandoFilaRegion.value) {
        const idEditando = filaRegionSeleccionada.value ? filaRegionSeleccionada.value.IdRegion : null;
        
        if (String(row.IdRegion) !== String(idEditando)) {
            mostrarNotificacion(
                "Aviso", 
                "Tiene una edición en curso, por favos guarda los datos o cancela (Esc) antes de seleccionar otro registro.", 
                "warning"
            );

            nextTick(() => {
                if (refTablaRegionesNombre.value && filaRegionSeleccionada.value) {
                    refTablaRegionesNombre.value.setCurrentRow(filaRegionSeleccionada.value);
                }
            });
            return; 
        }
    }

    filaRegionSeleccionada.value = row;
    idRegionSeleccionada.value = row.IdRegion;
    obsRegionTaxon.value = row.Observaciones || ''; 
};

const dialogFormVisibleTipoDist = ref(false);

const abrirCatalogoTipoDist = () => {
    dialogFormVisibleTipoDist.value = true;
};

const cerrarTipoDist = async () => {
    dialogFormVisibleTipoDist.value = false;
    const tiposDistrib = await axios.get('/carga-tipos-distribucion');
    if (tiposDistrib.status === 200) tiposDistribucion.value = tiposDistrib.data;
};

const totalRegionesNomComFull = computed(() => regionesNomComFull.value.length);

const obsRegionTaxon = ref('');

const dialogResumenRegionTaxonVisible = ref(false);
const citaCompletaResumen = ref('');
const refTablaRegionesModalResumen = ref(null);


const refTablaRegionesNombre = ref(null);
const filaRegionSeleccionada = ref(null);
const editandoFilaRegion = ref(false);
const filtroRegionesGeneral = ref('inicia');
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

const tablaCaractReg = ref([]);


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


const abrirResumenRegionTaxon = () => {
    idRegionSeleccionada.value = null;
    tablaBibliografiasRel.value = [];
    totalBibliografiasRel.value = 0;
    observaciones.value = "";
    citaCompletaResumen.value = "";
    editandoObs.value = false;
    dialogResumenRegionTaxonVisible.value = true;
    nextTick(() => {
    });
};

const clickRegResumenTaxon = async (row) => {
    if (!row) return;

    if (editandoObs.value) {
        mostrarNotificacion("Aviso", "Tiene una edición de observaciones activa. Guarde antes de cambiar de región.", "warning");
    
        nextTick(() => {
            if (refTablaRegionesModalResumen.value) {
                const filaOriginal = regionesNombre.value.find(r => r.IdRegion === idRegionSeleccionada.value);
                if (filaOriginal) {
                    refTablaRegionesModalResumen.value.setCurrentRow(filaOriginal);
                }
            }
        });
        return; 
    }

    idRegionSeleccionada.value = row.IdRegion;
    const idTipoDist = row.TipoDistribucion?.id || row.IdTipoDistribucion;
    if (!idTipoDist) return;

    try {
        const url = `/obtener-biblio-region-taxon/${props.taxonAct.id}/${row.IdRegion}/${idTipoDist}`;
        const response = await axios.get(url);
        if (response.status === 200) {
            tablaBibliografiasRel.value = response.data;
            totalBibliografiasRel.value = response.data.length;
            if (response.data.length > 0) {
                observaciones.value = response.data[0].Observaciones || "";
                citaCompletaResumen.value = response.data[0].CitaCompleta || "";
                idBiblioSeleccionada.value = response.data[0].IdBibliografia || response.data[0].id;
            } else {
                observaciones.value = "";
                citaCompletaResumen.value = "";
                idBiblioSeleccionada.value = null;
            }
        }
    } catch (error) {
        console.error("Error al cargar bibliografías:", error);
        tablaBibliografiasRel.value = [];
    }
};



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
    
]);

const colDefRegionCaract = ref([
    {
        prop: 'Region', 
        label: 'Región', 
        minWidth: '130',
        align: 'left', 
        tipo: 'Texto', 
        filtrable: true
    },
    {
        prop: 'TipoDistribucion', 
        label: 'Tipo Distribucion', 
        minWidth: '120',
        align: 'left', 
        tipo: 'lista', 
        filtrable: true
    },
    {
        prop: 'Biblio', 
        label: '', 
        minWidth: '56', 
        align: 'left',
        tipo: 'imagenTexto', 
        filtrable: false
    },
]);

const colDefRegionNomCom = ref([
    {
        prop: 'Region', label: 'Región', minWidth: '20',
        align: 'left', tipo: 'Texto', filtrable: true
    },
    {
        prop: 'Biblio', label: '', minWidth: '5', align: 'left',
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
        prop: 'TituloPublicacion', label: 'Título de la publicacion', minWidth: '100',
        align: 'center', tipo: 'texto', filtrable: true
    },
    {
        prop: 'TituloSubPublicacion', label: 'Título de la subpublicacion', minWidth: '100',
        align: 'center', tipo: 'texto', filtrable: true
    },
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

const seleccionarTipoRegionBase = (data, autoSelectFirst = true) => {
    if (!data) return;
    selectedTipoRegionNode.value = data;
    activePathIds.value = findPathInTree(tiposRegionTreeData.value, data.IdTipoRegion) || [];
    nextTick(() => {
        tiposRegionTreeRef.value?.setCurrentKey(data.IdTipoRegion);
        if (autoSelectFirst && filteredRegionsTree.value && filteredRegionsTree.value.length > 0) {
            const primerNodo = filteredRegionsTree.value[0];
            handleNodeSelected(primerNodo);
        } else if (!autoSelectFirst) {
            tiposRegionTreeRef.value?.setCurrentKey(data.IdTipoRegion);
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

const handleTipoRegionSelected = (data) => seleccionarTipoRegionBase(data, true);


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
  
        if (!nuevoValor?.id) return;
        try {
            const [respNomCom, respRegion] = await Promise.allSettled([
                axios.get(`/cargar-nomcomun-taxon/${nuevoValor.id}`),
                axios.get(`/cargaRegionesTaxon/${nuevoValor.id}`),
            ]);
    
            if (respNomCom.status === 'fulfilled' && respNomCom.value.status === 200) {
                const data = respNomCom.value.data;
                tablaNomComun.value = data;
                tablaNomComunAsociados.value = data;
                nombresAsociadosTaxon.value = data;
                totalRegNomComun.value = data.length;
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

const obtenerTipoDeRegionDesdeMaestro = (nodes, idRegion) => {
    for (const node of nodes) {
        if (node.IdRegion === idRegion) {
            return node.IdTipoRegion;
        }
        if (node.children && node.children.length > 0) {
            const found = obtenerTipoDeRegionDesdeMaestro(node.children, idRegion);
            if (found) return found;
        }
    }
    return null;
};


const clickNomCom = async (data) => {
    if (!data) return;
    observacionesGeneral.value = data.Observaciones || "";
    valorOriginalObsGeneral.value = observacionesGeneral.value;
    editandoObsGeneral.value = false;
    nodoSeleccionadoArbol.value = data;
    const targetId = data.IdNomComun || data.id;
    if (tablaNomComunPrincipalRef.value) {
        await tablaNomComunPrincipalRef.value.limpiarTodosLosFiltros();
        try {
            const res = await axios.post('/nombres-comunes/obtener-pagina', {
                id: targetId,
                perPage: 100
            });
            const targetPage = res.data.page;
            await tablaNomComunPrincipalRef.value.irAPagina(targetPage);
            await nextTick();
            let intentos = 0;
            const seleccionarYAenfocar = () => {
                const filaReal = tablaNomComun.value.find(r => 
                    String(r.IdNomComun) === String(targetId)
                );
                if (filaReal) {
                    tablaNomComunPrincipalRef.value.setCurrentRow(filaReal);
                    setTimeout(() => {
                        tablaNomComunPrincipalRef.value.forzarFocoFilaVerde();
                    }, 200);
                } else if (intentos < 5) {
                    intentos++;
                    setTimeout(seleccionarYAenfocar, 200);
                }
            };
            seleccionarYAenfocar();
        } catch (error) {
            console.error("No se pudo localizar el registro en la tabla:", error);
        }
    }

    if (!data.NombreComun) {
        idRegionSeleccionada.value = data.IdRegion;
        idNomComunSeleccionado.value = data.IdNomComun;
        tipoSeleccion.value = 'region';
        let idTipoRegion = data.IdTipoRegion || obtenerTipoDeRegionDesdeMaestro(localTreeDataReg.value, data.IdRegion);
        if (idTipoRegion) {
            const tipoNode = findNodeInTipoRegionTree(tiposRegionTreeData.value, idTipoRegion);
            if (tipoNode) {
                seleccionarTipoRegionBase(tipoNode, false);
                nextTick(() => {
                    if (treeRef.value) {
                        treeRef.value.setCurrentKey(data.IdRegion);
                        const node = treeRef.value.getNode(data.IdRegion);
                        if (node) { let p = node.parent; while (p) { p.expanded = true; p = p.parent; } }
                    }
                });
            }
        }
    } else {
        idNomComunSeleccionado.value = targetId;
        idRegionSeleccionada.value = null;
        tipoSeleccion.value = 'comun';
        tablaNomComunReg.value = data.Regiones || [];
    }
};

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

const Guardar = () => {

}

onMounted(async () => {
    window.addEventListener('keydown', handleKeyDown, true);
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

onUnmounted(() => {
     window.removeEventListener('keydown', handleKeyDown, true);
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

    .contenedor-rel-caract {
        width: 100%;
        height: 100%;
        min-width: 0;
        min-height: 0;
    }

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

.panel-nombre.table-wrapper :deep(.el-button + .el-button),
.panel-nombre.table-wrapper :deep(.el-button + span),
.panel-nombre.table-wrapper :deep(span + .el-button) {
    margin-left: 0 !important;
}
</style>
