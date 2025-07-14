import { h } from 'vue';
import { ElMessageBox } from 'element-plus';
import BotonAceptar from '@/Components/Biotica/BotonAceptar.vue';
import BotonCancelar from '@/Components/Biotica/BotonCancelar.vue';

export const showConfirmMessageFunc = ({
  title = 'Confirmar acción',
  message,
  icon = '!',
  confirmButtonText = 'Aceptar',
  cancelButtonText = 'Cancelar',
  onConfirm,
  onCancel
}) => {
  ElMessageBox({
    title,
    showConfirmButton: false,
    showCancelButton: false,
    customClass: 'message-box-diseno-limpio',
    message: h('div', { class: 'custom-message-content' }, [
      h('div', { class: 'body-content' }, [
        h('div', { class: 'custom-warning-icon-container' }, [
          h('div', { class: 'custom-warning-circle' }, icon)
        ]),
        h('div', { class: 'text-container' }, [h('p', null, message)])
      ]),
      h('div', { class: 'footer-buttons' }, [
        h(BotonCancelar, { 
          class: 'cancel-button', 
          onClick: () => {
            onCancel?.();
            ElMessageBox.close();
          }
        }, cancelButtonText),
        h(BotonAceptar, { 
          class: 'confirm-button', 
          onClick: () => {
            onConfirm?.();
            ElMessageBox.close();
          }
        }, confirmButtonText)
      ])
    ])
  }).catch(() => {
    onCancel?.();
  });
};

export const showConfirmMessage = ({
  title = 'Confirmar acción',
  message,
  icon = '!',
  confirmButtonText = 'Aceptar',
  cancelButtonText = 'Cancelar'
}) => {
  return new Promise((resolve) => {
    ElMessageBox({
      title,
      showConfirmButton: false,
      showCancelButton: false,
      customClass: 'message-box-diseno-limpio',
      message: h('div', { class: 'custom-message-content' }, [
        h('div', { class: 'body-content' }, [
          h('div', { class: 'custom-warning-icon-container' }, [
            h('div', { class: 'custom-warning-circle' }, icon)
          ]),
          h('div', { class: 'text-container' }, [h('p', null, message)])
        ]),
        h('div', { class: 'footer-buttons' }, [
          h(BotonCancelar, { 
            class: 'cancel-button', 
            onClick: () => {
              ElMessageBox.close();
              resolve(false);
            }
          }, cancelButtonText),
          h(BotonAceptar, { 
            class: 'confirm-button', 
            onClick: () => {
              ElMessageBox.close();
              resolve(true);
            }
          }, confirmButtonText)
        ])
      ])
    }).catch(() => {
      resolve(false);
    });
  });
};