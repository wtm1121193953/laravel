import React, {Component} from 'react';


class Footer extends Component {

    render(){
        return <div style={{bottom: '0', width: '100%', height: '60px', lineHeight: '60px', textAlign: 'center', 'color': '#475669'}} className="el-menu">基于ElementReact与Laravel5.5的后台管理系统, 由<a href="mailto:574583177@qq.com">EvanLee</a>提供技术支持</div>;
    }
}

export default Footer;