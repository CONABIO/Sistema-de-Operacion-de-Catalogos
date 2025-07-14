<template>
    <el-tooltip class="item" effect="dark" content="Salir" placement= "bottom"  :z-index="2101">
    <button class="boton-salir" @click="handleClick" aria-label="Salir">
        <svg version="1.0" id="Capa_1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink"
    x="0px" y="0px" width="40px" height="40px" viewBox="0 0 1200 1200" enable-background="new 0 0 1200 1200"
    xml:space="preserve">

    <path 
        fill="#000000" 
        d="M599.924,45.625c-305.671,0-554.299,248.778-554.449c0,305.672,248.628,554.3,554.299,554.3
	    c305.82,0,554.451-248.628,554.451-554.3C1154.375,294.403,905.744,45.625,599.924,45.625z" 
    />

    <path 
        fill="#FFFFFF"  
        d="M739.486,798.422
	    c-66.081,0-132.119,0-198.607,0c0,40.318,0,79.884,0,119.449c-0.343,0-0.679,0-1.018,0c-0.922-0.694-1.757-1.553-2.773-2.061
	    c-20.359-10.232-40.734-20.433-61.116-30.622c-27.628-13.808-55.268-27.58-82.889-41.398
	    c-28.082-14.046-56.081-28.263-84.287-42.052c-4.331-2.116-5.732-4.353-5.732-9.056c0.124-167.946,0.106-335.892,0.109-503.838
	    c0-2.197,0.07-4.394,0.109-6.593c145.536,0,291.07,0,436.606,0c0,65.805,0,131.611,0,197.881c-13.19,0-26.085,0-39.469,0
	    c0-52.474,0-105.019,0-157.923c-105.563,0-210.65,0-315.737,0c-0.101,0.255-0.204,0.51-0.309,0.763
	    c4.4,2.262,8.771,4.569,13.193,6.775c35.67,17.759,71.361,35.492,107.03,53.255c11.185,5.571,22.384,11.124,33.441,16.94
	    c1.382,0.727,2.235,2.925,2.79,4.619c0.452,1.372,0.094,3.015,0.094,4.538c0,114.724,0,229.449,0.004,344.173
	    c0,1.806,0,3.614,0,5.727c53.321,0,106.02,0,159.284,0c0-39.727,0-79.213,0-118.969c13.349,0,26.128,0,39.277,0
	    C739.486,692.641,739.486,745.152,739.486,798.422z M779.772,677.486c0-25.228,0-50.972,0-77.207c-66.25,0-131.967,0-198.295,0
	    c0-26.804,0-52.797,0-79.276c65.876,0,131.601,0,197.93,0c0-26.387,0-52.3,0-78.278c39.504,39.583,78.665,78.829,117.522,117.762
	    C858.333,599.03,819.057,638.252,779.772,677.486z" 
    />
</svg>

    </button>
    </el-tooltip>
    
</template>
<script setup>
import { defineEmits } from 'vue';

const emit = defineEmits(['salir']);

function handleClick() {
    emit('salir');
}
</script>

<style scoped>
.boton-salir {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 32px;
    height: 32px;
    background-color: #000000;
    border-radius: 50%;
    border: none;
    cursor: pointer;
    padding: 0;
    color: #ffffff;
    font-size: 24px;
    transition: background-color 0.2s ease-in-out, transform 0.1s ease-in-out;
}

.boton-salir:hover {
    background-color: #333333;
    transform: scale(1.05);
}

.boton-salir:active {
    transform: scale(0.95);
}
</style>