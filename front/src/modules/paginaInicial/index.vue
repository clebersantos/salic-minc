<template>
    <v-container>
        <v-layout
            row
            wrap
        >
            <v-flex
                xs12
                md6
                class="mb-4"
            >
                <buscar-projeto />
            </v-flex>
        </v-layout>
        <v-layout
            row
            wrap
        >
            <v-flex
                xs12
                md12
            >
                <Comunicados />
            </v-flex>
        </v-layout>
        <v-layout>
            <dashboard />
        </v-layout>
    </v-container>
</template>

<script>
import BuscarProjeto from './components/BuscarProjeto';
import Comunicados from './components/Comunicados';
import Dashboard from '@/modules/paginaInicial/components/Dashboard';

export default {
    name: 'PaginaInicial',
    components: { Dashboard, Comunicados, BuscarProjeto },
};
</script>
