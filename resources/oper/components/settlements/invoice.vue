<template>
    <page>
        <div>
            <el-radio v-model="form.invoice_type" label="1" border>上传电子发票</el-radio>
            <el-radio v-model="form.invoice_type" label="2" border>寄送纸质发票</el-radio>
        </div>
    </page>
</template>

<script>
    import api from '../../../assets/js/api'

    export default {
        props: {
            scope: {type: Object, required: true}
        },
        data() {
            return {
                form: {
                    invoice_type: 1,
                },
                invoice_type: 0,
            }
        },
        methods: {

        },
        created() {

        }
    }
</script>

<style scoped>

</style>