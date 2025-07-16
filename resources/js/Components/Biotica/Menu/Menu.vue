<script setup>
import { router } from '@inertiajs/vue3';
import { ref, onMounted, onUnmounted, computed, watch } from 'vue';
import { usePage } from '@inertiajs/vue3';

const page = usePage();
const permisos = computed(() => page.props.permisos || []);

const activeIndex2 = ref(null);
const cargando = ref(true);

const routeMap = {
    '2-1': 'autorTaxon.index',
    '2-2': 'grupoTaxonomico.index',
    '2-3': 'nombresComunes.index',
    '2-4': 'tiposDistribucion.index',
    '2-5': 'tipos-relacion.index', 
    '2-6': 'categorias-taxonomicas.index', 
    '2-7': 'caracteristicas-taxon.index',
    '2-8': 'tipos-region.index',
    '3-1': 'nombreTax.index',
    '4': 'bibliografias.index'
};

const handleSelect = (index) => {
    const routeName = routeMap[index];
    if (routeName) {
        router.visit(route(routeName));
    } else {
        console.warn(`No se ha definido una ruta para el índice de menú: ${index}`);
    }
};

const props = defineProps({
    active: Boolean,
    users: {
        type: Object,
        required: false,
    },
});

const screenWidth = ref(window.innerWidth);
const updateScreenWidth = () => {
    screenWidth.value = window.innerWidth;
};
const isSmallScreen = computed(() => screenWidth.value < 992);

onMounted(() => {
    window.addEventListener('resize', updateScreenWidth);
    verificarCarga();
});
onUnmounted(() => {
    window.removeEventListener('resize', updateScreenWidth);
});

const verificarCarga = () => {
    cargando.value = !!(permisos.value && permisos.value.length > 0);
};

const hasPermisos = (etiqueta) => {
    const permiso = permisos.value.find(item => item.NombreModulo === etiqueta);
    return permiso?.Visible || false;
};

watch(
    permisos,
    () => {
        verificarCarga();
    },
    { deep: true, immediate: true }
);

</script>

<template>
    <div class="pro-menu-wrapper">
        <el-menu class="professional-menu" :mode="isSmallScreen ? 'vertical' : 'horizontal'"
            :default-active="activeIndex2" @select="handleSelect" :ellipsis="false">
            <el-sub-menu index="2" v-if="hasPermisos('MnuCatalogos')">
                <template #title>Catálogos</template>
                <el-menu-item index="2-1" v-if="hasPermisos('MnuCatAutores')">Autoridades taxonómicas</el-menu-item>
                <el-menu-item index="2-2" v-if="hasPermisos('MnuCatGrpTax')">Grupos taxonómicos</el-menu-item>
                <el-menu-item index="2-3" v-if="hasPermisos('MnuCatNomCom')">Nombre común</el-menu-item>
                <el-menu-item index="2-4" v-if="hasPermisos('MnuCatTipDist')">Tipo de distribución</el-menu-item>
                <el-menu-item index="2-5" v-if="hasPermisos('MnuCatTipRel')">Tipo de relación</el-menu-item>
                <el-menu-item index="2-6" v-if="hasPermisos('MnuCatCatTax')">Categoría taxonómica</el-menu-item>
                <el-menu-item index="2-7" v-if="hasPermisos('MnuCatCatTax')">Características asociadas al taxón</el-menu-item>
                <el-menu-item index="2-8" >Tipo de región</el-menu-item>
            </el-sub-menu>

            <el-sub-menu index="3" v-if="hasPermisos('MnuNomenclatura')">
                <template #title>Nomenclatura</template>
                <el-menu-item index="3-1" v-if="hasPermisos('MnuNomCientifico')">Nombre científico</el-menu-item>
                <el-sub-menu index="3-2" v-if="hasPermisos('MnuAsociar')">
                    <template #title>Asociar con</template>
                    <el-menu-item index="3-2-1" v-if="hasPermisos('MnuAscNomComun')">Nombres comunes</el-menu-item>
                    <el-menu-item index="3-2-2"
                        v-if="hasPermisos('MnuAscCaracteristicas')">Características</el-menu-item>
                    <el-menu-item index="3-2-3" v-if="hasPermisos('MnuAscRegion')">Regiones</el-menu-item>
                </el-sub-menu>
            </el-sub-menu>

            <el-menu-item index="4" v-if="hasPermisos('MnuBibliografia')">
                Bibliografía
            </el-menu-item>
        </el-menu>

        <div class="menu-spacer"></div>
    </div>
</template>

<style>
:root {
    --pro-bg: #ffffff;
    --pro-text: #333745;
    --pro-accent: #00796B;
    --pro-accent-light: #e0f2f1;
    --pro-border: #eaeaea;
}

.pro-menu-wrapper {
    display: flex;
    justify-content: flex-start;
    align-items: center;
    background-color: var(--pro-bg);
    border-bottom: 1px solid var(--pro-border);
    padding: 0 1rem;
    width: 100%;
}

.menu-spacer {
    flex-grow: 1;
}

.professional-menu.el-menu {
    border: none !important;
    background-color: transparent !important;
    flex-grow: 0;
    flex-shrink: 0;
}

.professional-menu>.el-sub-menu>.el-sub-menu__title,
.professional-menu>.el-menu-item {
    position: relative;
    font-size: 16px;
    font-weight: 500;
    letter-spacing: 0.5px;
    color: var(--pro-text) !important;
    padding: 0 20px !important;
    height: 60px;
    line-height: 60px !important;
    border-bottom: none !important;
    background-color: transparent !important;
    transition: color 0.3s ease;
}

.professional-menu>.el-sub-menu>.el-sub-menu__title {
    padding-right: 35px !important;
}


.professional-menu>.el-sub-menu>.el-sub-menu__title::after,
.professional-menu>.el-menu-item::after {
    content: '';
    position: absolute;
    bottom: 12px;
    left: 20px;
    right: 20px;
    height: 2px;
    background-color: var(--pro-accent);
    transform: scaleX(0);
    transform-origin: center;
    transition: transform 0.3s cubic-bezier(0.19, 1, 0.22, 1);
}

.professional-menu>.el-sub-menu:hover>.el-sub-menu__title,
.professional-menu>.el-menu-item:hover {
    color: var(--pro-accent) !important;
}

.professional-menu>.el-sub-menu:hover>.el-sub-menu__title::after,
.professional-menu>.el-menu-item:hover::after,
.professional-menu>.el-sub-menu.is-active>.el-sub-menu__title::after,
.professional-menu>.el-menu-item.is-active::after {
    transform: scaleX(1);
}

.professional-menu>.el-sub-menu.is-active>.el-sub-menu__title,
.professional-menu>.el-menu-item.is-active {
    color: var(--pro-accent) !important;
}

.el-menu--popup {
    border: 1px solid var(--pro-border) !important;
    border-radius: 8px !important;
    box-shadow: 0 8px 24px rgba(0, 0, 0, 0.08) !important;
    padding: 6px !important;
}

.el-menu--popup .el-menu-item,
.el-menu--popup .el-sub-menu__title {
    color: var(--pro-text) !important;
    border-radius: 4px;
}

.el-menu--popup .el-menu-item:hover,
.el-menu--popup .el-sub-menu__title:hover {
    color: var(--pro-accent) !important;
    background-color: var(--pro-accent-light) !important;
}

.el-menu--popup .el-menu-item.is-active {
    color: var(--pro-accent) !important;
    background-color: var(--pro-accent-light) !important;
    font-weight: 600;
}
</style>