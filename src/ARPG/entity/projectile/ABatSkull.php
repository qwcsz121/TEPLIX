<?php

namespace ARPG\entity\projectile;

use ARPG\entity\ProjectileEntity;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\entity\EntityDamageByEntityEvent;

use pocketmine\Player;
use pocketmine\entity\Entity;
use pocketmine\network\protocol\AddEntityPacket;

use ARPG\entity\projectile\ABlazeFireball;

class ABatSkull extends ABlazeFireball{
 
 const NETWORK_ID = 82;
 
 public function attackOccurred($target, $damage){
  
  $pk = new AddEntityPacket();
  $pk->eid = Entity::$entityCount ++;
  $pk->type = 93;
  $pk->x = $this->x;
  $pk->y = $this->y;
  $pk->z = $this->z;
  $pk->speedX = $this->motionX;
  $pk->speedY = $this->motionY;
  $pk->speedZ = $this->motionZ;
  $pk->yaw = $this->yaw;
  $pk->pitch = $this->pitch;
  $pk->metadata = $this->dataProperties;
    
  foreach($this->level->getPlayers() as $player){
     
   $player->dataPacket($pk);
  
   $distance = $this->distance($player);
      
   if($distance <= 2){
     
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
  
  ProjectileEntity::spawnTo($player);
 }
 
}



























