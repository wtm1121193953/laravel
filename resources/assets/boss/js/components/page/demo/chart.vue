<template>
    <div>
        <!-- 页面路径 -->
        <el-breadcrumb class="p-b-10">
            <el-breadcrumb-item @click.native="$menu.change('/demo/index')">首页</el-breadcrumb-item>
            <el-breadcrumb-item @click.native="$menu.change('/demo/list')">示例模块</el-breadcrumb-item>
            <el-breadcrumb-item>图表页</el-breadcrumb-item>
        </el-breadcrumb>

        <!-- 页面内容部分 -->
        <el-row class="page-content">
            <div class="page-title">
                示例图表页
            </div>

            <el-col>
                <div class="echarts">
                    <IEcharts :option="bar" :loading="loading" @ready="onReady" @click="onClick"></IEcharts>
                </div>
                <el-button @click="doRandom">Random</el-button>
            </el-col>
        </el-row>
    </div>
</template>
<style scoped>
    .echarts {
        width: 400px;
        height: 400px;
    }
</style>
<script>
    import IEcharts from 'vue-echarts-v3/src/full.vue';
    export default {
        data: function () {
            return {
                loading: true,
                bar: {
                    title: {
                        text: 'ECharts Hello World'
                    },
                    tooltip: {},
                    xAxis: {
                        data: ['Shirt', 'Sweater', 'Chiffon Shirt', 'Pants', 'High Heels', 'Socks']
                    },
                    yAxis: {},
                    series: [{
                        name: 'Sales',
                        type: 'bar',
                        data: [5, 20, 36, 10, 10, 20]
                    }]
                }
            }
        },
        methods: {
            doRandom() {
                const that = this;
                let data = [];
                for (let i = 0, min = 5, max = 99; i < 6; i++) {
                    data.push(Math.floor(Math.random() * (max + 1 - min) + min));
                }
                that.loading = !that.loading;
                that.bar.series[0].data = data;
            },
            onReady(instance) {
                console.log(instance);
            },
            onClick(event, instance, echarts) {
                console.log(arguments);
            }
        },
        created: function () {

        },
        components: {
            IEcharts
        }
    }
</script>