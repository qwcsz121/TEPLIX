<?php

namespace ARPG\entity\projectile;

use ARPG\entity\ProjectileEntity;
use pocketmine\level\particle\SporeParticle;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\entity\EntityDamageByEntityEvent;

use pocketmine\Player;
use pocketmine\entity\Entity;
use pocketmine\entity\Effect;
use pocketmine\network\protocol\AddEntityPacket;

use ARPG\entity\projectile\ABlazeFireball;

class ABlueWitherSkull extends ABlazeFireball{
 
 const NETWORK_ID = 89;
 
 public function attackOccurred($target, $damage){
 
  for($number = 1; $number <= 27; $number ++){
     
   $this->getLevel()->addParticle(new SporeParticle($this->add(
    mt_rand(-2,2) + mt_rand(-100, 100) / 500,
    mt_rand(-2,2) + mt_rand(-100, 100) / 500,
    mt_rand(-2,2) + mt_rand(-100, 100) / 500)
   ));
  }  
    
  foreach($this->level->getPlayers() as $player){
      
   if(($distance = $this->distance($player)) <= 2){
     
    $ev = new EntityDamageByEntityEvent($this->shootingEntity, $player, EntityDamageEvent::CAUSE_PROJECTILE, ($damage/4)*$distance);
       
    if($player->attack($ev->getFinalDamage(), $ev) === true) $ev->useArmors();
    
    $player->addEffect(Effect::getEffect(20)->setDuration(mt_rand(10, 30)*20)->setVisible(0));
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











