//连接通讯
import {ElMessage} from "element-plus";
import { reactive ,watchEffect} from "vue";
const im = {
    //当前用户id
    id:0,
    state :reactive({
        //左侧工具栏
        leftTool:'message',
        //好友
        friendList:[],
        //会话
        recentList:[],
        //会话类型
        recentType:'msg',
        //当前会话id
        recentId:-1,
        //当前会话标题
        recentTitle:'',
        //聊天记录分页大小
        recordSize: 20,
        //当前会话内容
        msgList:[],
        //未读消息数量
        unReadNum:0,

    }),
    webSocket:null,
    //心跳定时器
    pongHealthTimer: null,
    //监听回调
    listens:[],
    connect: function () {
        this.webSocket = new WebSocket("ws://127.0.0.1:15555/?username=admin&password=b2e472a882c223386ab0fa4c35421467");
        this.webSocket.onmessage = e=>{
            const receive = JSON.parse(e.data)
            const action = receive.action
            const data = receive.data
            if(action === 'login'){
                this.id = data.info.id
                this.pongHealthTimer = setInterval(()=>{
                    this.send('ping')
                },30000)
            }
            this.listens.forEach(callback=>{
                callback(action,data)
            })
        }
        this.webSocket.onclose = (e) => {
            clearInterval(this.pongHealthTimer)
            ElMessage.error('客服通讯连接失败')
            setTimeout(() => {
                this.connect()
            }, 3000)
        }
    },
    //是否当前选中对象
    isSelectUser(item){
        if(item.msg_type === 'msg'){
            return this.state.recentId == item.from_uid
        }else if(item.msg_type === 'customerMsg'){
            return this.state.recentId == item.group_id
        }
        return false
    },
    //选择会话
    selectUser(item,index){
        this.state.leftTool = 'message'
        this.state.recentTitle = item.msg_type === 'customerMsg' ? item.user_nickname : item.nickname
        this.state.recentType = item.msg_type
        if(item.msg_type === 'msg'){
            this.state.recentId = item.from_uid
        }else if(item.msg_type === 'customerMsg'){
            this.state.recentId = item.group_id
        }
        this.state.recentList[index].unReadNum = 0
        this.send('msgRecord',{
            msg_type:this.state.recentType,
            to_uid:  this.state.recentId,
            msg_id: null,
            size: this.state.recordSize,
            date: null,
            keyword: null,
        })
    },
    //发送
    send: function (action, data = []) {
        this.webSocket.send(JSON.stringify({
            action: action,
            data: data
        }))
    },
    //监听
    onMessage:function (callback) {
        this.listens.push(callback)
    }
}
watchEffect(()=>{
    im.state.unReadNum = 0
    im.state.recentList.forEach(item => {
        im.state.unReadNum  += parseInt(item.unReadNum)
    })
})
export default im


