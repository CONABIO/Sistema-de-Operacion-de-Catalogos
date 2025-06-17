<script setup>
import { computed } from "vue";

const props = defineProps({
  node: {
    type: Object,
    required: true,
  },
  selectedNodeId: {
    type: [String, Number, null],
    default: null,
  },
  depth: {
    type: Number,
    default: 0,
  },
  expandedNodeIds: {
    type: Set,
    required: true,
  },
});

const emit = defineEmits(["node-selected", "toggle-expansion"]);

const hasChildren = computed(() => {
  return props.node.children && props.node.children.length > 0;
});

const isExpanded = computed(() => {
  return props.expandedNodeIds.has(String(props.node.IdCatNombre));
});

function toggleExpand() {
  if (hasChildren.value) {
    emit("toggle-expansion", String(props.node.IdCatNombre));
  }
}

function selectNode() {
  emit("node-selected", props.node);
}

function handleNodeLabelClick() {
  emit("node-selected", props.node);
  if (hasChildren.value) {
    emit("toggle-expansion", String(props.node.IdCatNombre));
    // console.log('TreeNode: Emitido toggle-expansion (desde texto) para', String(props.node.IdCatNombre));
  }
}

const indentStyle = computed(() => {
  return { paddingLeft: `${props.depth * 20}px` };
});

const isSelected = computed(
  () => String(props.node.IdCatNombre) === String(props.selectedNodeId)
);

const nodeClass = computed(() => {
  return `node-id-${props.node.IdCatNombre}`;
});
</script>

<template>
  <li class="tree-node-item" :class="[nodeClass, { 'expanded-parent-node': hasChildren && isExpanded }]">
    <div
      class="node-item-content"
      :style="indentStyle"
      @click="selectNode" 
      :class="{ selected: isSelected, 'has-children': hasChildren }"
      role="treeitem"
      :aria-selected="isSelected"
      :aria-expanded="hasChildren ? isExpanded : undefined"
    >
      <span
        v-if="hasChildren"
        @click.stop="toggleExpand" 
        class="node-toggler"
        :aria-label="isExpanded ? 'Colapsar' : 'Expandir'"
      >
        <svg v-if="!isExpanded" xmlns="http://www.w3.org/2000/svg" width="26" height="26" fill="currentColor" class="bi bi-caret-right-fill" viewBox="0 0 16 16">
          <path d="m12.14 8.753-5.482 4.796c-.646.566-1.658.106-1.658-.753V3.204a1 1 0 0 1 1.659-.753l5.48 4.796a1 1 0 0 1 0 1.506z"/>
        </svg>
        <svg v-else xmlns="http://www.w3.org/2000/svg" width="26" height="26" fill="currentColor" class="bi bi-caret-down-fill" viewBox="0 0 16 16">
          <path d="M7.247 11.14 2.451 5.658C1.885 5.013 2.345 4 3.204 4h9.592a1 1 0 0 1 .753 1.659l-4.796 5.48a1 1 0 0 1-1.506 0z"/>
        </svg>
      </span>
      <span v-else class="node-toggler-placeholder"></span>

      <span
        class="node-label-text"
        @click.stop="handleNodeLabelClick" 
      >
        {{ node.Descripcion }}
      </span>
    </div>

    <ul v-if="hasChildren && isExpanded" class="node-children-list" role="group">
      <TreeNode
        v-for="childNode in node.children"
        :key="childNode.IdCatNombre"
        :node="childNode"
        :selected-node-id="selectedNodeId"
        :depth="depth + 1"
        :expanded-node-ids="expandedNodeIds"
        @node-selected="emit('node-selected', $event)"
        @toggle-expansion="emit('toggle-expansion', $event)"
      />
    </ul>
  </li>
</template>

<style scoped>
.tree-node-item {
  list-style-type: none;
  margin: 0;
  padding: 0;
  margin-bottom: 1px;
}

.node-item-content {
  display: flex;
  align-items: center;
  padding-top: 5px;
  padding-bottom: 5px;
  padding-right: 8px; 
  border-radius: 6px;
  cursor: pointer;
  transition: background-color 0.15s ease-out, color 0.15s ease-out;
  position: relative;
}

.node-item-content:hover {
  background-color: #f4f6f8;
}

.node-item-content.selected {
  background-color: #e7f0fe; 
  font-weight: 500; 
}

.node-item-content.selected::before {
  content: "";
  position: absolute;
  left: 0; 
  top: 20%; 
  bottom: 20%;
  width: 3.5px;
  background-color: #007bff; 
  border-radius: 0 3px 3px 0; 
}


.node-toggler {
  margin-right: 4px; 
  display: inline-flex;
  align-items: center;
  justify-content: center;
  width: 25px; 
  height: 25px; 
  color: #5a6ac5; 
  user-select: none; 
  border-radius: 4px; 
  transition: background-color 0.1s ease-in-out, color 0.1s ease-in-out;
  flex-shrink: 0; 
}

.node-item-content:hover .node-toggler {
  color: #3c4a8c; 
}

.node-item-content.selected .node-toggler {
  color: #0056b3; 
}

.node-toggler:hover {
  background-color: #e9ecef; 
}

.node-toggler-placeholder {
  display: inline-block;
  width: 25px; 
  height: 25px; 
  margin-right: 4px; 
  flex-shrink: 0;
}

.node-label-text {
  flex-grow: 1;
  font-size: 0.925rem;
  line-height: 1.45;
  padding: 2px 4px; 
  border-radius: 3px; 
}
.node-item-content:not(.selected) .node-label-text:hover {
  background-color: rgba(0,0,0,0.03); 
}


.node-item-content.selected .node-label-text {
  color: #1c3d5a; 
}

.node-children-list {
  padding-left: 0; 
  margin: 0;
}
</style>