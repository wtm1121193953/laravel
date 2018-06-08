
import VueQuillEditor, { Quill } from 'vue-quill-editor'

import {container, ImageExtend, QuillWatch} from 'quill-image-extend-module'
import ImageResize from 'quill-image-resize-module'

Quill.register('modules/ImageExtend', ImageExtend)
Quill.register('modules/imageResize', ImageResize);

// require styles
import 'quill/dist/quill.core.css'
import 'quill/dist/quill.snow.css'
import 'quill/dist/quill.bubble.css'

const globalOptions = {
    placeholder: '请输入内容',
    modules: {
        imageResize: {},
        ImageExtend: {
            loading: true,
            name: 'file',
            action: '/api/upload/image',
            response: (res) => {
                return res.data.url
            }
        },
        toolbar: {  // 如果不上传图片到服务器，此处不必配置
            container: container,  // container为工具栏，此次引入了全部工具栏，也可自行配置
            handlers: {
                'image': function () {  // 劫持原来的图片点击按钮事件
                    QuillWatch.emit(this.quill.id)
                }
            }
        },
    }
}

export default {
    VueQuillEditor,
    globalOptions: globalOptions
}