// resources/js/composables/usePermisos.js
import { usePage } from '@inertiajs/vue3';

export default function usePermisos() {

    const page = usePage();

    const permisos = page.props.permisos || [];
    const usuario =  page.props.auth || [];

  return {
    permisos,
    usuario
  };
}