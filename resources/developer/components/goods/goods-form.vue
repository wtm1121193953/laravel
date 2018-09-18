<template>
    <el-row>
        <el-col :span="22">
            <el-form :model="form" label-width="120px" :rules="formRules" ref="form" @submit.native.prevent>
                <el-form-item prop="name" label="商品名称">
                    <el-input v-model="form.name"/>
                </el-form-item>
                <el-form-item prop="supplier_id" label="所属供应商">
                    <el-select filterable placeholder="请选择供应商" v-model="form.supplier_id">
                        <el-option
                                v-for="supplier in enableSuppliers"
                                :key="supplier.id"
                                :label="supplier.name"
                                :value="supplier.id"/>
                    </el-select>
                </el-form-item>
                <el-form-item prop="category_id" label="商品分类">
                    <el-select filterable placeholder="请选择商品分类" v-model="form.category_id">
                        <el-option
                                v-for="category in enableCategories"
                                :key="category.id"
                                :label="category.name"
                                :value="category.id"/>
                    </el-select>
                </el-form-item>
                <el-form-item label="商品规格" size="small">
                    <el-switch
                            v-model="form.useSpec"
                            active-text="开启规格"
                            inactive-text="不开启规格"
                    >
                    </el-switch>
                    <!-- 规格提示信息 -->
                    <el-alert
                            v-if="!form.useSpec"
                            title="您最多可以添加两种商品规格（如：颜色，尺码）如商品没有规格则不用开启"
                            style="line-height: normal; margin-top: 10px"
                            type="info"
                            :closable="false"
                    />
                    <el-alert
                            v-if="form.useSpec"
                            title="您最多可以添加两种规格（如：颜色和尺码）规格名称可以自定义 如只有一项规格另一项留空"
                            style="line-height: normal; margin-top: 10px"
                            type="info"
                            :closable="false"/>

                    <div style="margin-top: 15px;">
                        <el-form-item class="el-form--inline" v-if="!form.useSpec">
                            <el-form-item prop="purchase_price" label="采购价" label-width="none">
                                <el-input-number v-model="form.purchase_price"/>
                            </el-form-item>
                            <el-form-item prop="origin_price" label="售价" label-width="none">
                                <el-input-number v-model="form.origin_price"/>
                            </el-form-item>
                            <!--<el-form-item prop="discount_price" label="折扣价">
                                <el-input-number v-model="form.discount_price"/>
                            </el-form-item>-->
                            <el-form-item v-if="!data" prop="stock" label="库存数量" label-width="none">
                                <el-input-number v-model="form.stock"/>
                            </el-form-item>
                        </el-form-item>

                        <el-table v-if="form.useSpec" :data="form.specs" border stripe>
                            <template slot="append">
                                <div style="margin: 12px;">
                                    <el-button type="text" size="small" @click="addSpec"><i class="el-icon-plus"></i> 添加一条规格</el-button>
                                </div>
                            </template>

                            <el-table-column label="颜色" :render-header="renderSpecTableHeader">
                                <template slot-scope="scope">
                                    <el-input v-model="scope.row.spec_1"/>
                                </template>
                            </el-table-column>
                            <el-table-column label="尺寸" :render-header="renderSpecTableHeader">
                                <template slot-scope="scope">
                                    <el-input v-model="scope.row.spec_2"/>
                                </template>
                            </el-table-column>
                            <el-table-column label="采购价">
                                <template slot-scope="scope">
                                    <el-input-number v-model="scope.row.purchase_price"/>
                                </template>
                            </el-table-column>
                            <el-table-column label="售价">
                                <template slot-scope="scope">
                                    <el-input-number v-model="scope.row.origin_price"/>
                                </template>
                            </el-table-column>
                            <!--<el-table-column label="折扣价">
                                <template slot-scope="scope">
                                    <el-input v-model="scope.row.discount_price"/>
                                </template>
                            </el-table-column>-->
                            <el-table-column label="库存" prop="stock">
                                <template slot-scope="scope">
                                    <el-input-number v-model="scope.row.stock"/>
                                </template>
                            </el-table-column>
                            <el-table-column>
                                <template slot-scope="scope">
                                    <el-button type="text" :disabled="scope.$index === 0" @click="specMoveUp(scope)">
                                        <i class="el-icon-sort-up"></i>
                                    </el-button>
                                    <el-button type="text" :disabled="scope.$index === (form.specs.length - 1)" @click="specMoveDown(scope)">
                                        <i class="el-icon-sort-down"></i>
                                    </el-button>
                                    <el-button type="text" size="small" @click="delSpec(scope)">删除</el-button>
                                </template>
                            </el-table-column>
                        </el-table>
                    </div>
                </el-form-item>

                <el-form-item prop="default_image" label="商品图片">
                    <image-upload v-model="form.default_image" :limit="1"/>
                </el-form-item>
                <el-form-item prop="small_images" label="小图列表">
                    <image-upload v-model="form.small_images"/>
                </el-form-item>
                <el-form-item prop="detail" label="图文详情">
                    <quill-editor
                            v-model="form.detail"
                            ref="myQuillEditor"
                            :options="editorOption"
                    />
                </el-form-item>

                <el-form-item prop="status" label="状态">
                    <el-radio-group v-model="form.status">
                        <el-radio :label="1">正常</el-radio>
                        <el-radio :label="2">禁用</el-radio>
                    </el-radio-group>
                </el-form-item>
                <el-form-item>
                    <el-button @click="cancel">取消</el-button>
                    <el-button type="primary" @click="save">保存</el-button>
                </el-form-item>
            </el-form>
        </el-col>
    </el-row>

</template>
<script>
    import { mapGetters } from 'vuex'
    let defaultForm = {
        name: '',
        status: 1,
        supplier_id: '',
        category_id: '',
        useSpec: false,
        purchase_price: '',
        origin_price: '',
        discount_price: '',
        stock: 0,
        spec_name_1: '颜色',
        spec_name_2: '尺码',
        specs: [],
        default_image: '',
        small_images: '',
        detail: '',
    };
    export default {
        name: 'item-form',
        props: {
            data: Object,
        },
        computed:{
            ...mapGetters('goods', [
                "enableSuppliers",
                "enableCategories",
            ]),
        },
        data(){
            return {
                form: deepCopy(defaultForm),
                formRules: {
                    name: [
                        {required: true, message: '名称不能为空'}
                    ]
                },
                editorOption: {
                }
            }
        },
        methods: {
            specName1Change(v){ this.form.spec_name_1 = v },
            specName2Change(v){ this.form.spec_name_2 = v },
            renderSpecTableHeader(h, {column, $index}){
                return h('el-input', {
                    props: {
                        size: 'small',
                        value: $index === 0 ? this.form.spec_name_1 : this.form.spec_name_2,
                    },
                    on: {
                        input: $index === 0 ? this.specName1Change : this.specName2Change,
                    },
                })
            },
            // 添加规格
            addSpec(){
                this.form.specs.push({
                    spec_1: '',
                    spec_2: '',
                    purchase_price: '',
                    origin_price: '',
                    discount_price: '',
                    stock: 0,
                });
            },
            // 规格列表项上移
            specMoveUp(scope){
                console.log('up')
                this.form.specs.splice(scope.$index, 1)
                this.form.specs.splice(scope.$index-1, 0, scope.row)
            },
            // 规格列表项下移
            specMoveDown(scope){
                console.log('down')
                this.form.specs.splice(scope.$index, 1)
                this.form.specs.splice(scope.$index+1, 0, scope.row)
            },
            // 删除规格
            delSpec(scope){
                this.form.specs.splice(scope.$index, 1)
            },
            // 获取商品详情
            getDetail(){
                store.dispatch('openGlobalLoading')
                api.get('goods/detail', {id: this.data.id}).then(data => {
                    store.dispatch('closeGlobalLoading')
                    this.form = data;
                })
            },
            // 初始化表单
            initForm(){
                if(this.data){
                    this.form = deepCopy(this.data)
                    this.getDetail()
                }else {
                    this.form = deepCopy(defaultForm)
                }
            },
            cancel(){
                this.$emit('cancel');
            },
            save(){
                this.$refs.form.validate(valid => {
                    if(valid){
                        let data = deepCopy(this.form);
                        this.$emit('save', data);
                        setTimeout(() => {
                            // this.$refs.form.resetFields();
                        }, 500)
                    }
                })

            }
        },
        created(){
            this.initForm();
        },
        watch: {
            data(){
                this.initForm();
            }
        },
        components: {
        }
    }
</script>
<style scoped>

</style>
