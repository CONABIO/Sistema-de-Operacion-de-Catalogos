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
                                    <BotonCaract @click="abrirCaract" style="flex-shrink: 0; min-width: max-content;" />

                                    <BotonRegiones @click="abrirReg" style="flex-shrink: 0; min-width: max-content;" />

                                    <BotonNomComun @click="abrirNomCom"
                                        style="flex-shrink: 0; min-width: max-content;" />

                                    <BotonSalir accion="cerrar" @salir="closeDialog"
                                        style="flex-shrink: 0; min-width: max-content;" />
                                </div>
                            </el-col>
                        </el-row>
                        <el-tabs type="card" v-model="tabInicial">
                            <el-tab-pane label="Nombre(s) común(es)" name="NomComun">
                                <div style="height: 560px; border: 1px solid #ddd; border-radius: 8px; overflow: hidden; box-shadow: var(--el-border-color-light) 0px 0px 10px">
                                    <el-splitter lazy>
                                        <el-splitter-panel :size="'30%'">
                                            <div class="table-wrapper" style="height: 100%; padding: 5px;">
                                                <TablaFiltrable
                                                    v-model:datos="tablaNomComun"
                                                    v-model:totalItems="contRegNomCom"
                                                    endpoint="/busca-nombre-comun"
                                                    :columnas="columnasDefinidasNomCom"
                                                    :opciones-filtro="opcionesFiltroNomComun"
                                                    :itemsPerPage="100"
                                                    :mostrarAcci="false"
                                                    :alturaTabla="452"
                                                    :highlight-current-row="true"
                                                    :mostrarBiblio="false"
                                                    :mostrarNuevo="false"
                                                    :mostrarEditar="false"
                                                    :mostrarBorrar="false"
                                                    :mostrarSalir="false"
                                                    :mostrarNomComun="true"
                                                    @row-click="clickNomComun"
                                                    @abrirNomComun="abrirNomCom">
                                                    <template #expand-column>
                                                        <el-table-column type="expand">
                                                            <template #default="{ row }">
                                                                <div class="expand-content-detail" style="padding: 15px;">
                                                                    <p><strong>IdNomComun:</strong> {{ row.IdNomComun }}</p>
                                                                    <p><strong>IdOriginal:</strong> {{ row.IdOriginal }}</p>
                                                                    <p><strong>Catalogo:</strong> {{ row.Catalogo }}</p>
                                                                    <p><strong>FechaCaptura:</strong> {{ row.FechaCaptura }}</p>
                                                                    <p><strong>Observaciones:</strong> {{ row.Observaciones }}</p>
                                                                </div>
                                                            </template>
                                                        </el-table-column>
                                                    </template>
                                                </TablaFiltrable>
                                            </div>
                                        </el-splitter-panel>

                                        <el-splitter-panel :size="'30%'">
                                            <div style="height: 100%;">
                                                <el-splitter layout="vertical" style="height:100%;">
                                                    <el-splitter-panel>
                                                        <el-card class="panel-card list-panel" shadow="never">
                                                            <template #header>
                                                                <div class="header-container">
                                                                    <span class="details-header-title">Tipo de región</span>
                                                                    <el-button style="background-color: springgreen; margin-left:auto;" circle @click="handleManageTiposRegion">
                                                                        <IconoMundo />
                                                                    </el-button>
                                                                </div>
                                                            </template>
                                                            <div class="demo-tree panel-nombre">
                                                                <el-tree v-if="tiposRegionTreeData.length"
                                                                    ref="tiposRegionTreeRef" :key="treeKey"
                                                                    :data="tiposRegionTreeData"
                                                                    :props="{ children: 'children', label: 'Descripcion' }"
                                                                    node-key="IdTipoRegion" :highlight-current="true"
                                                                    :expand-on-click-node="true"
                                                                    :default-expanded-keys="expandedTipoRegionKeys"
                                                                    @node-click="handleTipoRegionSelected">
                                                                    <template #default="{ data }">
                                                                        <span :class="['nodo-tipo-region', { 'is-active-path': activePathIds.includes(data.IdTipoRegion) }]">
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
                                                                <div class="header-container">
                                                                    <span class="details-header-title">Región</span>
                                                                     <el-button style="margin-left: auto;" circle  >
                                                                        <BotonRegiones @click="abrirReg"/>
                                                                    </el-button>

                                                                </div>
                                                            </template>
                                                            <div class="demo-tree panel-nombre">
                                                                <el-tree v-show="filteredRegionsTree.length" ref="treeRef"
                                                                    :key="treeKey" :data="filteredRegionsTree"
                                                                    :props="{ children: 'children', label: 'NombreRegion' }"
                                                                    node-key="IdRegion"
                                                                    :highlight-current="true" :expand-on-click-node="true"
                                                                    @node-click="handleNodeSelected">
                                                                    <template #default="{ node, data }">
                                                                        <span :id="'region-node-' + data.IdRegion" class="nodo-texto">
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
                                        <el-splitter-panel :size="'30%'">
                                            <div class="table-wrapper" style="height: 100%; padding: 5px;">
                                                <TablaFiltrable
                                                    v-model:datos="tablaNomComun"
                                                    v-model:totalItems="contRegNomCom"
                                                    endpoint="/busca-nombre-comun"
                                                    :columnas="columnasDefinidasNomCom"
                                                    :opciones-filtro="opcionesFiltroNomComun"
                                                    :itemsPerPage="100"
                                                    :mostrarAcci="false"
                                                    :alturaTabla="452"
                                                    :highlight-current-row="true"
                                                    :mostrarBiblio="false"
                                                    :mostrarNuevo="false"
                                                    :mostrarEditar="false"
                                                    :mostrarBorrar="false"
                                                    :mostrarSalir="false"
                                                    :mostrarNomComun="true"
                                                    @row-click="clickNomComun"
                                                    @abrirNomComun="abrirNomCom">
                                                    <template #expand-column>
                                                        <el-table-column type="expand">
                                                            <template #default="{ row }">
                                                                <div class="expand-content-detail" style="padding: 15px;">
                                                                    <p><strong>Observaciones:</strong> {{ row.Observaciones }}</p>
                                                                    <p><strong>IdNomComun:</strong> {{ row.IdNomComun }}</p>
                                                                    <p><strong>IdOriginal:</strong> {{ row.IdOriginal }}</p>
                                                                    <p><strong>Catalogo:</strong> {{ row.Catalogo }}</p>
                                                                    <p><strong>FechaCaptura:</strong> {{ row.FechaCaptura }}</p>
                                                                </div>
                                                            </template>
                                                        </el-table-column>
                                                    </template>
                                                </TablaFiltrable>
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
                                                    <div class="table-wrapper" style="height: 100%; display: flex; flex-direction: column;">

                                                        <div style="flex: 1; padding: 10px; overflow: auto;">
                                                            <TablaFiltrable :columnas="columnasDefinidasCaract"
                                                                :datos="tablaCaracteristicas"
                                                                :opciones-filtro="opcionesFiltroCaract"
                                                                :totalItems="totalRegCaract" :itemsPerPage="9"
                                                                :mostrarBiblio="true" :mostrarAcci="false"
                                                                :alturaTabla="280" :highlight-current-row="true"
                                                                :mostrarNuevo="true" :mostrarEditar="true"
                                                                :mostrarBorrar="true" :mostrarSalir="false"
                                                                @row-click="clickCaract"
                                                                @abrir-Biblio="abrirResumenCaractSolo"
                                                                @nuevo-item="nuevoRelCaract"/>
                                                        </div>
                                                    </div>
                                                </el-splitter-panel>

                                                <el-splitter-panel :size="'30%'">
                                                    <div
                                                        style="padding: 15px; background: #fff; border-top: 2px solid #eee; height: 100%;">
                                                        <p
                                                            style="font-size: 13px; color: #333; margin-bottom: 8px; font-weight: bold;">
                                                            Observaciones taxon - característica</p>
                                                        <div style="display: flex; align-items: flex-start; gap: 10px;">
                                                            <el-input v-model="observacionesCaractGral" type="textarea"
                                                                disabled :rows="3"
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
                                                    <div class="table-wrapper" style="height: 100%; display: flex; flex-direction: column;">
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
                                                                @row-click="clickRegCaract"
                                                                @abrir-Biblio="abrirResumenCaract" />
                                                        </div>
                                                    </div>
                                                </el-splitter-panel>

                                                <el-splitter-panel :size="'30%'">
                                                    <div
                                                        style="padding: 15px; background: #fff; border-top: 2px solid #eee; height: 100%;">
                                                        <p
                                                            style="font-size: 13px; color: #333; margin-bottom: 8px; font-weight: bold;">
                                                            Observaciones de características - region</p>
                                                        <div style="display: flex; align-items: flex-start; gap: 10px;">
                                                            <el-input v-model="observaciones" type="textarea" :rows="3"
                                                                disabled placeholder="Observaciones de la región..."
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
                                                        :valoresOpcion="tiposDistribucion" :habOpciones="habOpciones"
                                                        :itemsPerPage=4 :mostrarBiblio="true" :mostrarAcci="false"
                                                        :alturaTabla=264 :highlight-current-row="true"
                                                        :mostrarNuevo="false" :mostrarRegion="true"
                                                        :mostrarGuardar="true" :mostrarEditar="true"
                                                        :mostrarBorrar="true" :mostrarSalir="false">
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
                                                        :valoresOpcion="tiposDistribucion" :habOpciones="habOpciones"
                                                        :itemsPerPage=4 :mostrarBiblio="true" :mostrarAcci="false"
                                                        :alturaTabla=288 :highlight-current-row="true"
                                                        :mostrarNuevo="false" :mostrarGuardar="true"
                                                        :mostrarEditar="true" :mostrarBorrar="false"
                                                        :mostrarSalir="false">
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
                                                <el-tree class="tree-full" :data="todasRegiones" :props="defaultProps">
                                                    <template #default="{ node, data }">
                                                        <Logo class="tree-node-logo" :rutaCategoria="data.Biblio.url" />
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
            <RelNomComun :modal="true" :taxonActual = props.taxonAct @cerrar="cerrarRelNomCom" />
        </DialogForm>

        <DialogForm v-model="dialogFormVisibleRelCaract" :botCerrar="true" :pressEsc="false" :width="'90%'">
            <RelCaract :modal="true" :taxonActual = props.taxonAct @cerrar="cerrarRelCaract"/>
        </DialogForm>

        <DialogForm v-model="dialogResumenRegionesVisible" :botCerrar="true" :pressEsc="true" :width="'85%'">
            <div style="height: 780px; background-color: #fff; display: flex; flex-direction: column; gap: 15px;">

                <el-header class="header">
                    <div class="header-content">
                        <h1 class="titulo">Asociación de nombre comun - región - bibliografía</h1>
                    </div>
                </el-header>

                <div style="display: flex; justify-content: space-between; align-items: center; padding: 0 5px;">
                    <el-row :gutter="21">
                        <el-col :span="18">
                            <span style="font-size: 18px; color: #8A2815; font-weight: bold;">
                                {{ props.taxonAct.label }}
                            </span>
                        </el-col>
                        <el-col :span="5">
                            <div style="display: flex; gap: 5px; justify-content: flex-end; margin-left: 380px;">
                                <BotonSalir accion="cerrar" @salir="cerrarModalesResumen"
                                    style="flex-shrink: 0; min-width: max-content;" />
                            </div>
                        </el-col>
                    </el-row>
                </div>

                <div style="flex: 1; min-height: 0;">
                    <el-splitter style="height: 100%; border: 1px solid #ddd; border-radius: 8px;">
                        <el-splitter-panel :min="20" :size="'25%'">
                            <div
                                class="table-wrapper" style="height: 100%; display: flex; flex-direction: column; border-right: 1px solid #eee;">
                                <div
                                    style="background-color: #f2b0b0; padding: 10px; text-align: center; border-bottom: 1px solid #ddd;">
                                    <span style="color: #8A2815; font-weight: bold;">Nombres Comunes</span>
                                </div>
                                <div style="flex: 1; padding: 10px; overflow: auto;">
                                    <TablaFiltrable :columnas="columnasDefinidasModal" :datos="tablaNomComun"
                                        :totalItems="totalRegNomComun" :alturaTabla="500" :highlight-current-row="true"
                                        :mostrarBiblio="false" :mostrarNuevo="false" :mostrarBorrar="false"
                                        :mostrarSalir="false" :mostrarEditar="false" @row-click="clickNomCom" />
                                </div>
                            </div>
                        </el-splitter-panel>

                        <el-splitter-panel>
                            <el-splitter>
                                <el-splitter-panel :min="20" :size="'33%'">
                                    <div
                                        class="table-wrapper" style="height: 100%; display: flex; flex-direction: column; border-right: 1px solid #eee;">
                                        <div
                                            style="background-color: #bae1f2; padding: 10px; text-align: center; border-bottom: 1px solid #ddd;">
                                            <span style="color: #4b7a94; font-weight: bold;">Regiones</span>
                                        </div>
                                        <div style="flex: 1; padding: 10px; overflow: auto;">
                                            <TablaFiltrable :columnas="columnasDefinidasRegNomCom"
                                                :datos="tablaNomComunReg" :totalItems="totalRegionNomComun"
                                                :alturaTabla="500" :highlight-current-row="true" :mostrarBiblio="false"
                                                :mostrarNuevo="false" :mostrarBorrar="false" :mostrarSalir="false"
                                                :mostrarEditar="false" @row-click="clickRegNomCom" />
                                        </div>
                                    </div>
                                </el-splitter-panel>

                                <el-splitter-panel>
                                    <el-splitter layout="vertical">
                                        <el-splitter-panel :min="50" :size="'40%'">
                                            <div class="table-wrapper" style="height: 100%; display: flex; flex-direction: column;">
                                                <div
                                                    style="background-color: #f4f4bf; padding: 10px; text-align: center; border-bottom: 1px solid #ddd; position: relative;">
                                                    <span style="color: #856404; font-weight: bold;">Bibliografía</span>
                                                </div>
                                                <div style="flex: 1; padding: 10px; overflow: auto;">
                                                    <TablaFiltrable :columnas="colDefBiblioFinal"
                                                        :datos="tablaBibliografiasRel"
                                                        :totalItems="totalBibliografiasRel" :alturaTabla="350"
                                                        :highlight-current-row="true" :mostrarNuevo="true"
                                                        :mostrarEditar="true" :mostrarBorrar="true"
                                                        :mostrarSalir="false" @row-click="clickBiblioRel"
                                                        @nuevo-item="abrirBiblio" @eliminar-item="eliminarBiblioRel"
                                                        @editar-item="habilitarEdicionObs" />
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
                                                    <GuardarButton @click="guardarCambiosObs"
                                                        :disabled="botonGuardarDeshabilitado" />
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
                            <el-header height="40px" style="display:flex; justify-content:center; align-items:center;">
                                <span style="font-size: 16px; color: #8A2815; font-weight: bold;">Característica</span>
                            </el-header>
                            <TablaFiltrable :columnas="columnasDefinidasCaract" :datos="tablaCaracteristicas"
                                :totalItems="totalRegCaract" :alturaTabla="450" :highlight-current-row="true"
                                @row-click="clickCaract" :mostrarBiblio="false" :mostrarNuevo="false"
                                :mostrarBorrar="false" :mostrarSalir="false" :mostrarEditar="false" />
                        </div>
                    </el-splitter-panel>

                    <el-splitter-panel :size="'24%'">
                        <div class="panel-nombre table-wrapper" style="height: 100%; border-radius: 8px;">
                            <el-header height="40px" style="display:flex; justify-content:center; align-items:center;">
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
                                <div style="padding: 15px; background: #fff; border-top: 2px solid #eee; height: 100%;">
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
            <div style="height: 830px; padding: 10px; background-color: #fff; display: flex; flex-direction: column;">

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
                                    <TablaFiltrable :columnas="columnasDefinidasCaract" :datos="tablaCaracteristicas"
                                        :totalItems="totalRegCaract" :alturaTabla="480" :highlight-current-row="true"
                                        @row-click="clickCaract" :mostrarBiblio="false" :mostrarNuevo="false"
                                        :mostrarBorrar="false" :mostrarSalir="false" :mostrarEditar="false" />
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
                                            <TablaFiltrable :columnas="colDefBiblioFinal" :datos="tablaBibliografiasRel"
                                                :totalItems="totalBibliografiasRel" :alturaTabla="320"
                                                :highlight-current-row="true" :mostrarNuevo="true"
                                                :mostrarEditar="false" :mostrarBorrar="true" :mostrarSalir="false"
                                                @nuevo-item="abrirBiblio" @eliminar-item="eliminarBiblioRel"
                                                @row-click="clickBiblioRel" />
                                        </div>
                                    </div>
                                </el-splitter-panel>

                                <el-splitter-panel :size="'20%'">
                                    <div
                                        style="padding: 15px; background: #fff; border-top: 2px solid #eee; height: 100%;">
                                        <p style="font-size: 13px; color: #333; margin-bottom: 8px; font-weight: bold;">
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
import BotonSalir from '@/Components/Biotica/SalirButton.vue';
import BotonCaract from '@/Components/Biotica/BtnCaracteristicas.vue';
import BotonRegiones from '@/Components/Biotica/BtnRegiones.vue';
import BotonNomComun from '@/Components/Biotica/BtnNomComunes.vue';
import DialogForm from '@/Components/Biotica/DialogGeneral.vue';
import CuerpoCaracteristicas from '@/Pages/Socat/Caracteristicas/CuerpoCaracteristicas.vue';
import CuerpoRegion from '@/Pages/Socat/Regiones/CuerpoRegion.vue';
import RelNomComun from '@/Pages/Socat/RelCatalogosAsociados/RelacionNomComun.vue';
import CuerpoNombreCom from '@/Pages/Socat/Nombres/CuerpoNombreComun.vue';
import CuerpoTipoRegion from '@/Pages/Socat/TipoRegion/CuerpoTipoRegion.vue';
import CuerpoBibliografia from '@/Pages/Socat/Bibliografia/CuerpoBibliografia.vue';
import TablaFiltrable from "@/Components/Biotica/TablaFiltrable.vue";
import { onMounted, ref, watch, computed, h, nextTick } from 'vue';
import GuardarButton from '@/Components/Biotica/GuardarButton.vue';
import Logo from '@/Components/Biotica/LogoCategoria.vue';
import { ElMessageBox } from 'element-plus';
import BotonAceptar from '@/Components/Biotica/BotonAceptar.vue';
import BotonCancelar from '@/Components/Biotica/BotonCancelar.vue';
import NotificacionExitoErrorModal from "@/Components/Biotica/NotificacionExitoErrorModal.vue";
<<<<<<< HEAD
import IconoMundo from '@/Components/Biotica/IconoMundo.vue';
=======
import RelCaract from '@/Pages/Socat/RelCatalogosAsociados/RelacionCaracteristicas.vue';
>>>>>>> 504df032ced02d886bb2942ad4e39316bf14250c

const cerrarModalesResumen = () => {
    dialogResumenRegionesVisible.value = false;
    dialogResumenCaractVisible.value = false;
    dialogResumenCaractSoloVisible.value = false;
    idBiblioSeleccionada.value = null;
    editandoObs.value = false;
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
    console.log("Intentando guardar... ID:", idNomComunSeleccionado.value);

    if (!idNomComunSeleccionado.value) {
        console.error("ERROR: No hay ID seleccionado");
        return;
    }

    const proceder = async () => {
        console.log("Entró a proceder con el guardado");
        ElMessageBox.close();
        try {
            const payload = {
                IdNomComun: idNomComunSeleccionado.value,
                Observaciones: observacionesGeneral.value
            };
            await axios.put('/actualizar-obs-nomcomun-base', payload);
            mostrarNotificacion("Éxito", "Observación actualizada", "success");
            editandoObsGeneral.value = false;
            const index = tablaNomComun.value.findIndex(n => (n.IdNomComun || n.id) === idNomComunSeleccionado.value);
            if (index !== -1) {
                tablaNomComun.value[index].Observaciones = observacionesGeneral.value;
            }
        } catch (error) {
            console.error("Error en la petición axios:", error);
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
                    h('p', null, "¿Deseas guardar los cambios en las observaciones generales de este nombre común?")
                ])
            ]),
            h('div', { class: 'footer-buttons' }, [
                h(BotonCancelar, {
                    onClick: () => ElMessageBox.close(),
                    onConfirmar: () => ElMessageBox.close()
                }),
                h(BotonAceptar, {
                    onClick: proceder,
                    onConfirmar: proceder
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

            mostrarNotificacion("Éxito", "Observación actualizada", "success");
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
    clickBiblioRel(row);
    editandoObs.value = true;
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
                mostrarNotificacion("Éxito", "Observación guardada correctamente", "success");
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
    editandoObs.value = false;
    console.log("======= DATOS SELECCIONADOS =======");
    console.log("IdNombre (Taxón):", props.taxonAct.id);
    console.log("IdNomComun:", idNomComunSeleccionado.value);
    console.log("IdRegion:", idRegionSeleccionada.value);
    console.log("IdBibliografia:", row.IdBibliografia || row.id);
    idBiblioSeleccionada.value = row.IdBibliografia || row.id;
    observaciones.value = row.Observaciones || row.observaciones || "";
    console.log("Observación cargada:", observaciones.value);
    console.log("====================================");
};

const dialogResumenRegionesVisible = ref(false);
const dialogResumenCaractVisible = ref(false);

const abrirResumenRegiones = () => {
    dialogResumenRegionesVisible.value = true;
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
    console.log("Esto es claseOrigen: ", data.origen);
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

const tabInicial = ref("NomComun");
const idRegionSeleccionada = ref(null);
const idCaractSeleccionada = ref(null);
const idRegionCaractSeleccionada = ref(null);
const idTipoDistSeleccionada = ref(null);
const tablaBiblioCaract = ref([]);
const totalBiblioCaract = ref(0);

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

const observacionesGeneral = ref('');
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

    const filterAndPruneTree = (nodes) => {
        return nodes.reduce((accumulator, node) => {
            if (node.IdTipoRegion === targetTypeId) {
                accumulator.push({ ...node, children: [] });
            } else if (node.children && node.children.length > 0) {
                const filteredChildren = filterAndPruneTree(node.children);
                if (filteredChildren.length > 0) {
                    accumulator.push({ ...node, children: filteredChildren });
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
        if (tiposRegionTreeData.value.length > 0 && !selectedTipoRegionNode.value) seleccionarPrimeroPorDefault();
    }
}

const onCreaRelacion = async () => {
    if (!idNomComunSeleccionado.value || !selectedNode.value) {
        mostrarNotificacion("Error", "La selección de nombre común y región es obligatoria.", "error");
        return;
    }
    const params = {
        idNombre: props.taxonAct.id,
        idNomComun: idNomComunSeleccionado.value,
        idTipoReg: selectedNode.value.IdTipoRegion,
        idRegion: selectedNode.value.IdRegion
    };
    try {
        const response = await axios.post(`/alta-relNom-Nomcomun`, params);
        if (response.status === 200) mostrarNotificacion('Aviso', response.data.message, 'success');
    } catch (error) { console.error(error); }
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
    async (nuevoValor, valorAnterior) => {
        const respNomCom = await axios.get(`/cargar-nomcomun-taxon/${props.taxonAct.id}`);

        if (respNomCom.status === 200) {
            tablaNomComun.value = respNomCom.data;
            totalRegNomComun.value = respNomCom.data.length;
        }

        const listCaract = await axios.get(`/cargaCaracTaxon/${props.taxonAct.id}`);

        if (listCaract.status === 200) {
            console.log("Estas son las caracteristicas: ", listCaract);
            tablaCaracteristicas.value = listCaract.data;
            totalRegCaract.value = listCaract.data.length;
        }

        const regionTaxon = await axios.get(`/cargaRegionesTaxon/${props.taxonAct.id}`);

        if (regionTaxon.status === 200) {
            regionesNombre.value = regionTaxon.data.regPorNombre;
            totalRegionesNom.value = regionTaxon.data.regPorNombre.length;
            console.log("Regiones Nombre: ", regionesNombre.value);
            regionesCaract.value = regionTaxon.data.regPorCaract;
            totalRegionesNom.value = regionTaxon.data.regPorCaract.length;
            console.log("Regiones Caracteristicas: ", regionesCaract.value);
            regionesNomCom.value = regionTaxon.data.regPorNomCom;
            totalRegionesNom.value = regionTaxon.data.regPorCaract.length;
            console.log("Regiones nombre comun: ", regionesNomCom.value);
        }

    }
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
    idNomComunSeleccionado.value = row.IdNomComun || row.id;
    observacionesGeneral.value = row.Observaciones || row.observaciones || row.ObsNomCom || "";
    tablaNomComunReg.value = row.Regiones || [];
    totalRegionNomComun.value = tablaNomComunReg.value.length;
    editandoObsGeneral.value = false;
}

const clickNomComun = (row) => clickNomComunOriginal(row);
const clickNomCom = (row) => clickNomComunOriginal(row);

const clickCaract = (row) => {
    idCaractSeleccionada.value = row.IdCatNombre || row.id;
    tablaCaractReg.value = row.Regiones || [];
    totalRegionCaract.value = (row.Regiones || []).length;
    idRegionCaractSeleccionada.value = null;
    idTipoDistSeleccionada.value = null;
    cargarBibliografiasRelCaractSolo();
};

const clickRegCaract = async (row) => {
    console.log("Datos crudos de la región recibidos:", row);
    idRegionCaractSeleccionada.value = row.IdRegion;
    if (row.TipDistribucion && typeof row.TipDistribucion === 'object') {
        idTipoDistSeleccionada.value = row.TipDistribucion.IdTipoDistribucion || row.TipDistribucion.id;
    } else {
        idTipoDistSeleccionada.value = row.IdTipoDistribucion;
    }
    console.log("ID Región:", idRegionCaractSeleccionada.value);
    console.log("ID Tipo Distribución:", idTipoDistSeleccionada.value);
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


const clickRegNomCom = async (row) => {
    console.log("Datos de la región seleccionada:", row);
    editandoObsReg.value = false;
    idRegionSeleccionada.value = row.IdRegion || row.id || row.IdCatRegion;
    idBiblioSeleccionada.value = null;
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
    console.log("IDs para consulta:", {
        Comun: idNomComunSeleccionado.value,
        Region: idRegionSeleccionada.value,
        Taxon: props.taxonAct.id
    });
    if (idNomComunSeleccionado.value && idRegionSeleccionada.value && props.taxonAct.id) {
        try {
            const url = `/obtener-biblio-nomcomun-region/${idNomComunSeleccionado.value}/${idRegionSeleccionada.value}/${props.taxonAct.id}`;
            const response = await axios.get(url);
            tablaBibliografiasRel.value = response.data;
            totalBibliografiasRel.value = response.data.length;
            if (response.data.length > 0) {
                observaciones.value = response.data[0].Observaciones || response.data[0].observaciones || "";
            }
        } catch (error) {
            console.error("Error al traer datos:", error);
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
    console.log("le di click al boton de nuevo")
    dialogFormVisibleRelNomCom.value = true;
    console.log("Este es el valor de dialogRolNomComun: ", dialogFormVisibleRelNomCom.value);
}

const nuevoRelCaract = () => {
    dialogFormVisibleRelCaract.value = true;
}

const Guardar = () => {
    console.log("Esta es la funcion de guardar");
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

.nodo-texto, .nodo-tipo-region {
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
    background: rgb(241, 189, 189);
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

.table-wrapper :deep(.el-table__row.current-row) > td {
    background-color: #ddf6dd !important;
}

.table-wrapper :deep(.el-table__row.current-row) .cell {
    color: #007bff !important;
    font-weight: bold !important;
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
</style>
