<?php

namespace ARPG;


use pocketmine\plugin\PluginBase;
use pocketmine\event\Listener;

use pocketmine\command\{Command,CommandSender};

use pocketmine\Player;
use pocketmine\item\Item;
use pocketmine\block\Block;
use pocketmine\math\Vector3;

use pocketmine\event\player\PlayerChatEvent;
use pocketmine\event\player\PlayerJoinEvent;

use pocketmine\utils\Config;
use pocketmine\entity\Entity;
use pocketmine\entity\Effect;
use pocketmine\entity\XPOrb;
use pocketmine\level\Position;

use pocketmine\event\entity\{EntityDeathEvent,EntityDamageEvent,EntityDamageByEntityEvent};

use pocketmine\nbt\tag\{ByteTag,CompoundTag,DoubleTag,FloatTag,ListTag,StringTag,IntTag};

use pocketmine\item\enchantment\Enchantment;

use ARPG\DataList;
use ARPG\Commands;
use ARPG\entity\BaseEntity;
use ARPG\particle\FloatingText;

use ARPG\entity\projectile\{
 ABlazeFireball,
 ABlueWitherSkull,
 ADragonFireBall,
 ABatSkull,
 AShulkerSkull};

use ARPG\entity\monster\flying\{
 ABat,
 ABlaze,
 AGhast,
 AWitherBoss,
 AEnderDragon};

use ARPG\entity\monster\walking\{
 AChicken,
 ARabbit,
 ASheep,
 ACow,
 AMooshroom,
 AOcelot,
 APig,
 ASilverfish,
 AWolf,
 ACaveSpider,
 ACreeper,
 AEnderman,
 ASpider,
 AHorse,
 ADonkey,
 ASkeletonHorse,
 AZombieHorse,
 AMule,
 ASquid,
 AVillager,
 AWitch,
 ASnowGolem,
 AIronGolem,
 AGuardian,
 Aelderguardian,
 APolarBear,
 AEndermite,
 AShulker,
 ASlime,
 ALavaSlime};

use ARPG\entity\monster\walking\EMods\{
 AZombie,
 ASkeleton,
 AStray,
 AHusk,
 APigZombie,
 AWitherSkeleton,
 ANPC};

class Main extends PluginBase implements Listener{
 
 public $detector = [];
 public $spawnTmp = [];
 
 private static $instance;
 
 public function onEnable()
 {
  $classes = [
   ABlazeFireball::class,
   ABlueWitherSkull::class,
   ADragonFireBall::class,
   ABatSkull::class,
   AShulkerSkull::class,
   ABat::class,
   ABlaze::class,
   AGhast::class,
   AWitherBoss::class,
   AEnderDragon::class,
   AChicken::class,
   ARabbit::class,
   ASheep::class,
   ACow::class,
   AMooshroom::class,
   AOcelot::class,
   APig::class,
   ASilverfish::class,
   AWolf::class,
   ACaveSpider::class,
   ACreeper::class,
   AEnderman::class,
   ASpider::class,
   AHorse::class,
   ADonkey::class,
   ASkeletonHorse::class,
   AZombieHorse::class,
   AMule::class,
   ASquid::class,
   AVillager::class,
   AWitch::class,
   ASnowGolem::class,
   AIronGolem::class,
   AGuardian::class,
   Aelderguardian::class,
   APolarBear::class,
   AEndermite::class,
   AShulker::class,
   ASlime::class,
   ALavaSlime::class,
   AZombie::class,
   ASkeleton::class,
   AStray::class,
   AHusk::class,
   APigZombie::class,
   AWitherSkeleton::class,
   ANPC::class
  ];
 
  foreach($classes as $name){
 
   Entity::registerEntity($name);
  }
 
  self::$instance = $this;
 
  @mkdir($this->getDataFolder());
  @mkdir($this->getDataFolder()."/skins");
  @mkdir($this->getDataFolder()."/skins/cache");
   
  $this->skinConfig = new Config($this->getDataFolder()."/skins/"."Config.yml", Config::YAML, ["来自神奇的贴吧" => "CampfireTales_CampfireTalesOlDiggy"]);
  $this->grade = new Config($this->getDataFolder()."Grade.yml", Config::YAML, []);
  $this->exp = new Config($this->getDataFolder()."Exp.yml", Config::YAML, ['提示' => '玩家首次升级需要300经验,升到2则300*2
  升到3则300*3经验,以此类推!']);
  $this->skinId = $this->skinConfig->getAll();
  
  $this->Config = new Config($this->getDataFolder()."Config.yml", Config::YAML, [
  '【友情提示】' => 'true=真/开   false=假/关',
   "刷新时间" => 2,
   "内存优化" => true,
   "掉落经验球" => true,
   "掉落发送背包" => false,
   "怪物装备掉落" => true,
   "内存优化间隔秒数" => 1200,
   "怪物超出范围提示" => true,
   "开关怪物出生点名字" => true,
   "怪物离开边界消失开关" => true,
   "创造模式是否可攻击怪" => false,
   //"是否允许玩家同一高度打怪" => true,
   '掉落发送提示类型有' =>"tip , message , popup",
   "掉落发送背包提示类型" => "tip", 
   "掉落发送背包提示" => "§e击杀成功,掉落物品已发送到你的背包",
   '创造提示打怪类型有' =>"tip , message , popup",
   "创造打怪提示类型" => "message",
   "创造打怪提示语" => "§c创造模式不能打怪 !",
   "玩家升级全服通告" => true,
   
   ]  );
  
  $this->RPGSpawn = new Config($this->getDataFolder()."RPGSpawn.yml", Config::YAML, []);
  
  $this->arpg = $this->RPGSpawn->getAll();
  
  
  
  $this->getCommand("ma")->setExecutor(new Commands($this));
 
  $this->getServer()->getScheduler()->scheduleRepeatingTask(new CallbackTask([$this,"onRefresh"]),$this->Config->get("刷新时间")*20);
  
  if($this->Config->get("内存优化") === true){
   
   $this->getServer()->getScheduler()->scheduleRepeatingTask(new GcTask($this), $this->Config->get("内存优化间隔秒数")*20);
  }
  
  $this->getServer()->getPluginManager()->registerEvents($this, $this);
  
   $this->getServer()->getLogger()->info("§6[ MagicalRPG ] §f来自神奇百度贴吧修复!!!");
  $this->getServer()->getLogger()->info("§b[ MagicalRPG ] §f插件加载成功!!!");
 }
 
      
    public function onJoin(PlayerJoinEvent $event){ //第一次进服创建玩家初始数据
  	    $name = $event->getPlayer()->getName();
	    if(!$this->exp->exists($name)){
	    
			$this->exp->set($name,0);//经验
			$this->grade->set($name,1);//等级
			$this->grade->save();
			$this->exp->save();
			$this->getServer()->getLogger()->info("成功创建玩家 $name 的数据");
	    }
    }
 
 
    public function onChat(PlayerChatEvent $event) {//聊天
        $event->setCancelled();
        $msg = $event->getMessage();
	    $name = $event->getPlayer()->getName();
		foreach($this->grade->getAll() as $ver => $key){
			if($name == $ver){
 $this->getServer()->broadcastMessage('§6[§aLV.'.$key.'§6]§e->§f'.$name.': ' .$msg);
				
			}
		}
	}

 /**
  保存皮肤文件…
 */
 public function saveSkin($skinName, $skinData){

  $file = $this->getDataFolder() . '/skins/cache/' . $skinName;
  $file = fopen($file, "w");
  fwrite($file, base64_encode($skinData));
  fclose($file);
 }
 
 /**
  得到皮肤数据…
 */
 public function getSkin($skinName){

  $file = $this->getDataFolder() . '/skins/cache/' . $skinName;
 
  if(file_exists($file)) return base64_decode(file_get_contents($file));
 }
 
 /**
  得到皮肤名字…
 */
 public function getSkinId($skinName){

  return (isset($this->skinId[$skinName]))? $this->skinId[$skinName]: "(๑•ั็ω•็ั๑)";
 }

 public function onRefresh(){
  
  foreach ($this->arpg as $data){

   $no_player = true;
   $vector3 = new Vector3($data["x"],$data["y"],$data["z"]);
      
   foreach($this->getServer()->getOnlinePlayers() as $player){
  
    $level = $player->getLevel();
    
    if($data["世界"] === $level->getFolderName() and $player->distance($vector3) <=  $data["边界范围半径"]){
    
     $no_player = false;
    
     if(!isset($this->spawnTmp[$data["刷怪点"]]["数量"])) $this->spawnTmp[$data["刷怪点"]]["数量"] = 0;
    
     if($this->spawnTmp[$data["刷怪点"]]["数量"] < (int)$data["数量"]){     
      
      $nbt = new CompoundTag;
      $nbt->Pos = new ListTag("Pos",[
       new DoubleTag("", $vector3->x),
       new DoubleTag("", $vector3->y + 0.5),
       new DoubleTag("", $vector3->z)
      ]);
      $nbt->Rotation = new ListTag("Rotation",[
       new FloatTag("", 0),
       new FloatTag("", 0)
      ]);
     
      /**
       怪物随机装备.
      */
      if(in_array($data["类型"], DataList::$EMods)){
       
       $armorType = mt_rand(0, 6);
    
       $nbt->Held = new ListTag("Held", [
        new IntTag("",mt_rand(0, 2) == 2? mt_rand(267, 286) : 0),
        new IntTag("",mt_rand(9, 14)),
        new IntTag("",mt_rand(-3, 3))
       ]);
       $nbt->Cap = new ListTag("Cap", [
        new IntTag("",mt_rand(0, 2) == 2? $this->getArmor($armorType, 0) : 0),
        new IntTag("",mt_rand(0, 8)),
        new IntTag("",mt_rand(-3, 3))
       ]);
       $nbt->Tunic = new ListTag("Tunic", [
        new IntTag("",mt_rand(0, 2) == 2? $this->getArmor($armorType, 1) : 0),
        new IntTag("",mt_rand(0, 5)),
        new IntTag("",mt_rand(-3, 3))
       ]);
       $nbt->Pants = new ListTag("Pants", [
        new IntTag("",mt_rand(0, 2) == 2? $this->getArmor($armorType, 2) : 0),
        new IntTag("",mt_rand(0, 5)),
        new IntTag("",mt_rand(-3, 3))
       ]);
       $nbt->Boots = new ListTag("Boots", [
        new IntTag("",mt_rand(0, 2) == 2? $this->getArmor($armorType, 3) : 0),
        new IntTag("",mt_rand(0, 8)),
        new IntTag("",mt_rand(-3, 3))
       ]);
       
      }
      
      $nbt->Speed = new DoubleTag("Speed", $data["速度"]);
      
      if($data["类型"] == "npc"){
     
       $skin = $this->getSkinId($data["皮肤"]);
       
       $skinData = ($skin === "CampfireTales_CampfireTalesOlDiggy")? base64_decode(DataList::$skin): $this->getSkin($skin);
       
       $nbt->Skin = new CompoundTag("Skin", ["Data" => new StringTag("Data", $skinData), "Name" => new StringTag("Name", $skin)]);
      
      }
      
      $pk = Entity::createEntity(DataList::$ModName[$data["类型"]], (\pocketmine\API_VERSION === "3.0.1")? $level: $player->chunk, $nbt);
      
      $pk->arpg = $data;
      
      $pk->setMaxHealth($data["血量"]);
      $pk->setHealth($data["血量"]);
      $pk->spawnToAll();
      
      $pk->setNameTag($data["名字"]." §b§l【§e{$pk->getHealth()}§f/§4{$pk->getMaxHealth()}§b§l】§f");
       
      $pk->setDataProperty(39,Entity::DATA_TYPE_FLOAT,$data["范围"]);
      $pk->setNameTagVisible(true);
      $pk->setNameTagAlwaysVisible(true);
      
      $this->spawnTmp[$data["刷怪点"]]["数量"] ++ ;
     }
    }
   }
   
   $level = $this->getServer()->getLevelByName($data["世界"]);
   
   /**
    @检测范围内没有玩家,生物超出刷怪点范围,将生物清除.
    @在清除的地方生成一个浮空提示,持续1.5秒.
   */
   foreach($level->getEntities() as $entity){
    
    if($entity instanceof BaseEntity)
    if($entity->arpg["刷怪点"] === $data["刷怪点"]){
     
     if($no_player){
      if($this->Config->get("怪物超出范围提示") == true) {
      $particle = new FloatingText($entity->getPosition(), "§b范围内无玩家,重置刷怪点");
      $this->getServer()->getScheduler()->scheduleDelayedTask(new CallbackTask([$this,"removeText"],[$particle]), 30);
      $particle->spawn();
     }
      $entity->close();
      
      



 
     }elseif($entity->distance($vector3) >= $data["边界范围半径"]){  
        if($this->Config->get ("怪物离开边界消失开关") == true) {
      
      $particle = new FloatingText($entity->getPosition(), "§6怪物离开边界已消失");
      $particle->spawn();
      $this->getServer()->getScheduler()->scheduleDelayedTask(new CallbackTask([$this,"removeText"],[$particle]), 30);
}
      $entity->close();
      
      
     }
    }   
   }
   
   /**
    @持续刷新的浮空字标识
    */
   if($this->Config->get("开关怪物出生点名字") == true) {
   if(!isset($this->spawnTmp[$data["刷怪点"]]["标识"])){
   
    $particle = new FloatingText(new Position($data["x"],$data["y"],$data["z"],$level),"§c{$data["刷怪点"]}:§f {$data["名字"]}");
    
    $particle->spawn();
    
    $this->spawnTmp[$data["刷怪点"]]["标识"] = $particle;
    
   }else{
   
    $this->spawnTmp[$data["刷怪点"]]["标识"]->setText("§c{$data["刷怪点"]}:§f {$data["名字"]}");
  }  
   }
  
  }
 }
 
 public function removeText($particle){
 
  $particle->respawn(); unset($particle);
 }
 
 public function getArmor($armorType, $type){
  
  if($armorType <= 4){
   
   return DataList::$ModArmor[$armorType][$type];
  }
  
  return 0;
 }
 

/**
 @实体受到伤害
*/

 public function onEntityDamageEvent(EntityDamageEvent $ev){

  $entity = $ev->getEntity();
  
  if(!$ev instanceof EntityDamageByEntityEvent || !($entity instanceof BaseEntity)) return;
   if($this->Config->get ("创造模式是否可攻击怪") == false) {
  $p = $ev->getDamager();
      if ($p->getGamemode() != 0) {
        $p->sendMessage("§c创造模式不允许打怪！");
        
        $ev->setCancelled();
        switch ($this->Config->get("创造打怪提示类型")){
		   case "tip":
		   $player->sendTip($this->Config->get("创造打怪提示语"));
		   break;
		     case "popup":
		   $player->sendPopup($this->Config->get("创造打怪提示语"));
		   break;
		   
		   case "message":
		   $player->sendMessage($this->Config->get("创造打怪提示语"));
		   break;
		   }
     
      
      }
      }
  
  if(!($entity instanceof Player) || $entity->getGamemode() != 0) return;

  $ev->setDamage($damager->arpg["攻击"]);
  $entity->setOnFire($damager->arpg["燃烧"]);
  foreach($damager->arpg["药水"] as $potion){
       
   $attribute = explode(":",$potion);
     
   if(!isset($attribute[2]) or $attribute[0] < 0 or $attribute[0] > 25) return;
     
   $effect = Effect::getEffect((int)$attribute[0])->setDuration((int)$attribute[1]*20)->setAmplifier((int)$attribute[2]);
      
   $entity->addEffect($effect);
      
   $Color = $effect->getColor();
      
   $entity->level->addParticle(new \pocketmine\level\particle\SpellParticle($entity,$Color[0],$Color[1],$Color[2],255));   
  }
  
  unset($ev, $entity);
 }

/**
 @实体死亡
*/

 public function onEntityDeath(EntityDeathEvent $ev){
 
  $entity = $ev->getEntity();
  $level = $entity->getLevel();
 
  if(!($entity instanceof BaseEntity)) return;
  
  $cause = $entity->getLastDamageCause();
 
  if(!$cause instanceof EntityDamageByEntityEvent || !($player = $cause->getDamager()) instanceof Player || $player->getGameMode() !== 0) return;
  //级
    foreach($entity->participants as $name){
    
		$exp = $this->exp->getAll();
		$grade = $this->grade->getAll();
	    $player = $this->getServer()->getPlayer($name);
        if($player instanceof Player){
  $player->sendMessage('§f你已获得§c ' .$entity->arpg["经验"]. ' §f点经验');
			$this->exp->set($name,$this->exp->get($name) + $entity->arpg["经验"]);
			
			foreach($grade as $gver => $gkey){
				foreach($exp as $ver => $key){
					if($name == $ver){
						if($key >= 300 * $gkey){		
							$this->grade->set($name, $gkey + 1);
							$this->exp->set($name, $key - 300 * $gkey);
							if($this->Config->get ("玩家升级全服通告") == true)  {
		   $this->getServer()->broadcastMessage('§c§l恭喜玩家§a '.$name. ' §c成功升级!');
		   }
		   //if($this->Config->get ("玩家升级个人通告") == true)  {
		   //$player->sendMessage('§a恭喜你升级!');
		
						}
					}
				}
		    }
		    $this->exp->save();
		    $this->grade->save();
		    
	    }
    }
  
  if($this->Config->get("掉落经验球")){
   
   $nbt = new CompoundTag;
   $nbt->Pos = new ListTag("Pos",[
    new DoubleTag("", $entity->x),
    new DoubleTag("", $entity->y),
    new DoubleTag("", $entity->z)
   ]);
   $nbt->Rotation = new ListTag("Rotation",[
    new FloatTag("", 0),
    new FloatTag("", 0)
   ]);
   $nbt->Experience = new IntTag("Experience", $entity->arpg["经验"]);
   
   $xpord = new XPOrb((\pocketmine\API_VERSION === "3.0.1")? $level: $entity->chunk, $nbt);
   $xpord->spawnToAll();
  }
  
  foreach($entity->arpg["掉落"] as $drop){
  
   $dropItem = explode(":",$drop);   
   
   if(!isset($dropItem[5])) return;
   
   $item = new Item((int)$dropItem[0],(int)$dropItem[1],(int)$dropItem[2]);
   
   if(mt_rand(0,100) <= $dropItem[3]){
   
    if($dropItem[4] >= 0 and $dropItem[4] <= 24 and $dropItem[5] > 0){
   
     $enchantment = Enchantment::getEnchantment($dropItem[4])->setLevel($dropItem[5]);
     $item->addEnchantment($enchantment);
    
    }
   
    if(isset($dropItem[6])) $item->setCustomName($dropItem[6]); 
    
    if($this->Config->get("掉落发送背包")){ 
    
     foreach($entity->participants as $name){
     
      if(($player = $this->getServer()->getPlayer($name)) instanceof Player && $player->level === $entity->level){
      
       
       $player->getInventory()->addItem($item);
 
       $player->sendTip("§e击杀成功,掉落物品已发送到你的背包");
       switch ($this->Config->get("掉落发送背包提示类型")){
		   case "tip":
		   $player->sendTip($this->Config->get("掉落发送背包提示"));
		   break;
		     case "popup":
		   $player->sendPopup($this->Config->get("掉落发送背包提示"));
		   break;
		   
		   case "message":
		   $player->sendMessage($this->Config->get("掉落发送背包提示"));
		   break;
		   }
      
      }
     }
    
    }else{
    
     $level->dropItem($entity, $item);
     }
    
    
   }
  }
  
  /**
   @装甲掉落
  */
  if(!in_array($entity->arpg["类型"], DataList::$EMods) || !$this->Config->get("怪物装备掉落") || $this->Config->get("掉落发送背包")) return;
  
  $Armor = ["Held","Cap","Tunic","Pants","Boots"];
   
  foreach($Armor as $dropArmor){
      
   $item = $this->addEnchant($entity->namedtag[$dropArmor]);
   
   $level->dropItem($entity,$item); 
  }
  
  unset($ev, $entity, $level);
 }
 
 public function addEnchant($array){
     
  $item = new Item($array[0],0,1);
     
  if($array[0] <> 0 and $array[2] > 0){
 
   
   $enchantment = Enchantment::getEnchantment($array[1])->setLevel($array[2]);
   $item->addEnchantment($enchantment);
  }
  
  return $item;
 }
 
}

class CallbackTask extends \pocketmine\scheduler\Task{

 protected $callable;
 protected $args;
 
 public function __construct(callable $callable, array $args = []){
 
  $this->callable = $callable;
  $this->args = $args;
  $this->args[] = $this;
 }
	
	 public function getCallable(){
	 
	  return $this->callable;
	 }
	 
	 public function onRun($currentTicks){
	 
	  \call_user_func_array($this->callable, $this->args);
	 }
}

class GcTask extends \pocketmine\scheduler\PluginTask{
 
 public function __construct(Main $owner){
  
  parent::__construct($owner);
 }
 
 public function onRun($tick){
  
  $memory = memory_get_usage();
  $server = $this->owner->getServer();
  
  foreach($server->getLevels() as $level){
    
   $level->doChunkGarbageCollection();
   $level->unloadChunks(true);
   $level->clearCache(true);
  }
  
  $server->getMemoryManager()->triggerGarbageCollector();
  
  $server->getLogger()->info("§9[MagicalRPG] §f释放内存: ". \number_format(\round((($memory - \memory_get_usage()) / 1024) / 1024, 2))." MB");
 }
}































