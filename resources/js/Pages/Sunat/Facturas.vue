<template>
  <app-layout>
    <template #header>
      <div class="grid grid-cols-1 md:grid-cols-10">
        <div class="col-span-9">
          <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Facturas
          </h2>
        </div>        
      </div>
    </template>
    <div class="py-12">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
          <div class="flex flex-col">
            <div class="-my-2 overflow-x-auto sm:-mx-6 lg:-mx-8">
              <div
                class="
                  py-2
                  align-middle
                  inline-block
                  min-w-full
                  sm:px-6
                  lg:px-8
                "
              >
                <div
                  class="
                    shadow
                    overflow-hidden
                    border-b border-gray-200
                    sm:rounded-lg
                  "
                >
                  <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                      <tr>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                          ID VENTA
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                          CLIENTE
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                          VENDEDOR
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                          TIPO COMPROBANTE
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                          NUM. COMPROBANTE
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                          ESTADO
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                          OPCIÓN
                        </th>
                      </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                      <tr
                        v-for="factura in facturas"
                        :key="factura.id"
                      >
                        <td class="px-2 py-4 whitespace-nowrap">
                          <div class="flex items-center">
                            <div class="ml-4">
                              <div class="text-sm font-medium text-gray-900">
                                {{ factura.id }}
                              </div>
                            </div>
                          </div>
                        </td>
                        <td class="px-2 py-4 whitespace-nowrap">
                          <div class="flex items-center">
                            <div class="ml-4">
                              <div class="text-sm font-medium text-gray-900">
                                {{ factura.cliente.nombre }}
                              </div>
                            </div>
                          </div>
                        </td>
                        <td class="px-2 py-4 whitespace-nowrap">
                          <div class="flex items-center">
                            <div class="ml-4">
                              <div class="text-sm font-medium text-gray-900">
                                {{ factura.vendedor.usuario }}
                              </div>
                            </div>
                          </div>
                        </td>
                        <td class="px-2 py-4 whitespace-nowrap">
                          <div class="flex items-center">
                            <div class="ml-4">
                              <div class="text-sm font-medium text-gray-900">
                                {{ factura.tipo_comprobante }}
                              </div>
                            </div>
                          </div>
                        </td>
                        <td class="px-2 py-4 whitespace-nowrap">
                          <div class="flex items-center">
                            <div class="ml-4">
                              <div class="text-sm font-medium text-gray-900">
                                {{ factura.serie_comprobante }} - {{ factura.num_comprobante }}
                              </div>
                            </div>
                          </div>
                        </td>
                        <td class="px-2 py-4 whitespace-nowrap">
                          <div class="flex items-center">
                            <div class="ml-4">
                              <div class="text-sm font-medium text-gray-900">
                                {{ factura.estado }}
                              </div>
                            </div>
                          </div>
                        </td>
                        <td class="px-2 py-4 whitespace-nowrap">
                          <div class="flex items-center">
                            <div class="ml-4">
                              <div class="text-sm font-medium text-gray-900">
                                <jet-nav-link :href="route('sunat.enviar', factura.id)">
                                    Enviar
                                </jet-nav-link>
                              </div>
                            </div>
                          </div>
                        </td>
                      </tr>
                    </tbody>
                  </table>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </app-layout>
</template>

<script>
import AppLayout from "@/Layouts/AppLayout";
import JetNavLink from '@/Jetstream/NavLink.vue'

export default {
  name: "sunat.facturas",
  props: ["facturas"],
  components: {
    AppLayout,
    JetNavLink
  },
  data() {
    return {};
  },
  created() {
      
  }
};
</script>
