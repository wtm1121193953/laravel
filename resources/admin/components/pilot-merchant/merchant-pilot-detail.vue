<template>
    <el-row>
        <el-col :span="24">
            <el-form label-width="120px" label-position="left" size="small">
                <el-form-item prop="status" label="商户状态">
                    <span v-if="data.status === 1" class="c-green">已启用</span>
                    <span v-else-if="data.status === 2" class="c-danger">已冻结</span>
                    <span v-else>未知 ({{data.status}})</span>
                </el-form-item>
                <el-form-item prop="id" label="商户ID">{{data.id}}</el-form-item>
                <el-form-item v-if="data.operName" prop="operName" label="运营中心">{{data.operName}}</el-form-item>
                <el-form-item prop="name" label="商户名称">{{data.name}}</el-form-item>
                <el-form-item prop="signboard_name" label="招牌名称">{{data.signboard_name}}</el-form-item>
                <el-form-item prop="merchant_category" label="所属行业">
                    <span v-for="item in data.categoryPath" :key="item.id">
                        {{ item.name }}
                    </span>
                </el-form-item>
                <el-form-item prop="audit_status" label="审核状态">
                    <span v-if="data.audit_status === 0" class="c-warning">待审核</span>
                    <span v-else-if="data.audit_status === 1" class="c-green">审核通过({{data.active_time}})</span>
                    <span v-else-if="data.audit_status === 2" class="c-danger">审核不通过</span>
                    <span v-else-if="data.audit_status === 3" class="c-warning">重新提交审核</span>
                    <span v-else>未知 ({{data.audit_status}})</span>
                </el-form-item>
                <el-form-item prop="location" label="商户坐标">
                    <qmap-choose-point width="60%" height="300px" :shown-markers="[[data.lng, data.lat]]" disabled/>
                </el-form-item>
                <el-form-item prop="operAddress" label="运营中心地址">
                    {{data.operAddress}}
                </el-form-item>
                <el-form-item prop="area" label="省市区">
                    {{data.province}} {{data.city}} {{data.area}}
                </el-form-item>
                <el-form-item prop="address" label="详细地址">
                    {{data.address}}
                </el-form-item>
                <el-form-item prop="oper_biz_member_code" label="签约人">
                    <template>
                                <span v-if="data.bizer_id && data.bizer">
                                    <span>{{data.bizer.name}}</span>
                                    <span>{{data.bizer.mobile}}</span>
                                    <span>(业务员)</span>
                                </span>
                        <span v-else-if="data.oper_biz_member_code && data.operBizMember">
                                    <span>{{data.operBizMember.name}}</span>
                                    <span>{{data.operBizMember.mobile}}</span>
                                    <span>(员工)</span>
                                </span>
                        <span v-else>无</span>
                    </template>
                </el-form-item>
                <el-form-item prop="service_phone" label="客服电话">
                    {{data.service_phone}}
                </el-form-item>
                <el-form-item prop="logo" label="商家logo">
                    <div v-viewer>
                        <img :src="data.logo" alt="商家logo" style="max-width: 190px;" height="190px"  />
                    </div>
                </el-form-item>
                <el-form-item prop="desc_pic" label="商家介绍图片">
                    <div class="desc" v-viewer style="display: none;">
                        <img v-for="(item,index) in data.desc_pic_list" :src="item" :key="index" />
                    </div>
                    <el-button v-if="data.desc_pic_list.length > 0" type="text" @click="previewImage('desc')">查看</el-button>
                </el-form-item>
                <el-form-item prop="contacter" label="负责人姓名">
                    {{data.contacter}}
                </el-form-item>
                <el-form-item prop="contacter_phone" label="负责人手机号码">
                    {{data.contacter_phone}}
                </el-form-item>
                <el-form-item prop="business_licence_pic_url" label="营业执照">
                    <div class="desc" v-viewer >
                        <img :src="data.business_licence_pic_url" :key="index" style="max-width: 200px;" height="100px"  />
                    </div>
                </el-form-item>
                <el-form-item prop="organization_code" label="营业执照代码">
                    {{data.organization_code}}
                </el-form-item>

                <el-col v-if="type != 'poolOnly' ">
                    <el-form-item prop="audit_suggestion" label="审核意见">
                        <el-input v-if="(data.audit_status == 0 || data.audit_status == 3) && auditType == 3" placeholder="最多输入50个汉字"  maxlength="50" v-model="data.audit_suggestion" :autosize="{minRows: 3}" type="textarea"/>
                        <span v-else>{{data.audit_suggestion}}</span>
                    </el-form-item>
                </el-col>

                <!-- 商户激活信息右侧块 -->
                <el-col v-if="auditType == 3 && type != 'poolOnly'"  >
                    <el-form-item v-if="data.audit_status == 0 || data.audit_status == 3">
                        <el-button type="success" @click="audit(1)">审核通过</el-button>
                        <el-button type="warning" @click="audit(2)">审核不通过</el-button>
                        <el-button type="primary" @click="back()">返回</el-button>
                    </el-form-item>
                </el-col>

                <el-col  v-else >
                    <el-form-item >
                        <el-button type="primary" @click="back()">返回</el-button>
                    </el-form-item>
                </el-col>
            </el-form>

        </el-col>
    </el-row>

</template>

<script>
    import QmapChoosePoint from '../../../assets/components/qmap/qmap-choose-point'
    import api from '../../../assets/js/api'
    import 'viewerjs/dist/viewer.css'

    export default {
        name: 'merchant-pilot-detail',
        props: {
            data: Object,
            type: String,
            auditType:Number,
        },
        computed:{

        },
        data(){
            return {
                isShowPreviewImage: false,
                currentPreviewImage: '',
            }
        },
        methods: {
            previewImage(viewerEl){
                const viewer = this.$el.querySelector('.' + viewerEl).$viewer
                viewer.show()
            },
            audit(type){
                api.post('/merchant/audit', {id: this.data.id, type: type,audit_suggestion:this.data.audit_suggestion}).then(data => {
                    this.$alert(['', '审核通过', '审核不通过', '打回商户池'][type] + ' 成功');
                    this.$emit('change')
                })
            },
            back(){
                this.$emit('change')
            }
        },
        created(){
        },
        components: {
            QmapChoosePoint,
        }
    }

</script>

<style scoped>
    .title {
        font-weight: 600;
        line-height: 50px;
    }
</style>