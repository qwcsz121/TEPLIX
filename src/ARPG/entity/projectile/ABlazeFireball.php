<?php

namespace ARPG\entity\projectile;

use ARPG\entity\ProjectileEntity;
use pocketmine\level\particle\HugeExplodeParticle;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\network\protocol\AddEntityPacket;
use pocketmine\Player;
use pocketmine\entity\Entity;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\entity\EntityDamageByEntityEvent;

class ABlazeFireball extends ProjectileEntity{
 const NETWORK_ID = 94;

 public $width = 0.3125;
 public $height = 0.3125;
 protected $damage = 0;
 protected $drag = 0.01;
 protected $gravity = 0.05;
 protected $isCritical;
 protected $canExplode = false;

 public function __construct($world, CompoundTag $nbt, Entity $shootingEntity, bool $critical = false){
  
  $world = (\pocketmine\API_VERSION === "3.0.1")? $world: $shootingEntity->chunk;
  
  parent::__construct($world, $nbt, $shootingEntity);
  $this->isCritical = $critical;
 }

 public function isExplode() : bool{
  return $this->canExplode;
 }

 public function setExplode(bool $bool){
  $this->canExplode = $bool;
 }
 
 public function setDamage($damage){
  $this->damage = $damage;
 }

 public function onUpdate($currentTick){
  if($this->closed){
   return false;
  }

  $this->timings->startTiming();
  $hasUpdate = parent::onUpdate($currentTick);
  if($this->onGround){
   $this->isCritical = false;
  }
  if($this->age > 1200 or $this->isCollided){
   if($this->isCollided and $this->canExplode){
    
    $this->attackOccurred("o>_<o", $this->damage);
   }
   $this->kill();
   $hasUpdate = true;
  }
  $this->timings->stopTiming();
  return $hasUpdate;
 }
 
 public function attackOccurred($target, $damage){
 
  for($number = 1; $number <= 27; $number ++){
     
   $this->getLevel()->addParticle(new HugeExplodeParticle($this->add(
    mt_rand(-2,2) + mt_rand(-100, 100) / 500,
    mt_rand(-2,2) + mt_rand(-100, 100) / 500,
    mt_rand(-2,2) + mt_rand(-100, 100) / 500)
   ));
  }  
    
  foreach($this->level->getPlayers() as $player){
      
   if(($distance = $this->distance($player)) <= 2){
     
    $ev = new EntityDamageByEntityEvent($this->shootingEntity, $player, EntityDamageEvent::CAUSE_PROJECTILE, ($damage/2)*$distance);
       
    if($player->attack($ev->getFinalDamage(), $ev) === true) $ev->useArmors();
   }
  }
  
  return true;
 }

 public function spawnTo(Player $player){
  $pk = new AddEntityPacket();
  $pk->eid = $this->getId();
  $pk->type = self::NETWORK_ID;
  $pk->x = $this->x;
  $pk->y = $this->y;
  $pk->z = $this->z;
  $pk->speedX = $this->motionX;
  $pk->speedY = $this->motionY;
  $pk->speedZ = $this->motionZ;
  $pk->yaw = $this->yaw;
  $pk->pitch = $this->pitch;
  $pk->metadata = $this->dataProperties;
  $player->dataPacket($pk);
  parent::spawnTo($player);
 }

}















