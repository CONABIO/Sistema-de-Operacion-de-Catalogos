<template>
    <div>
        <div style="height: 830px; padding: 10px; background-color: #fff; display: flex; flex-direction: column;">

                <el-header class="header">
                    <div class="header-content">
                        <h1 class="titulo">Asociación de característica - bibliografía</h1>
                    </div>
                </el-header>

                <div style="padding: 15px 5px;">
                    <span style="font-size: 18px; color: #8A2815; font-weight: bold;">
                        {{ props.taxonActual.label }}
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
    </div>
</template>