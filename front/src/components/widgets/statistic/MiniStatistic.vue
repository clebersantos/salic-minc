<template>
    <v-card>
        <v-card-text class="pa-0">
            <v-container class="pa-0">
                <v-row>
                    <v-col
                        sm="6"
                        xs="6"
                    >
                        <div class="layout column ma-0 justify-center align-center">
                            <v-icon
                                size="56px"
                                :color="color"
                            >
                                {{ icon }}
                            </v-icon>
                        </div>
                    </v-col>
                </v-row>
                <v-col
                    sm="6"
                    xs="6"
                    class="text-center py-3 white--text"
                    :class="color"
                >
                    <div class="headline">
                        {{ title }}
                    </div>
                    <span class="caption">{{ subTitle }}</span>
                </v-col>
            </v-container>
        </v-card-text>
    </v-card>
</template>

<script>
export default {
    props: {
        icon: {
            type: String,
            default: '',
        },
        title: {
            type: String,
            default: '',
        },
        subTitle: {
            type: String,
            default: '',
        },
        color: {
            type: String,
            default: '',
        },
    },
};
</script>
