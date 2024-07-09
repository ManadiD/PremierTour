
<?php	


require "creer.html";
require "PHPLib/chargement.php";
require "fpdf181/fpdf.php";
require "PHPLib/premierTour_library.php";

if(isset($_POST['ok']) && empty($erreurs)){
	
################################################
# NOMBRE DE PARTICIPANTS :

$fichier=fopen("uploaded_files/".$file_name,"r");
#Test sur nombre des participants 
$nbl=0;
while($uneline=fgetcsv($fichier,1000,",")){
$nbl++;
}
$nblines=$nbl-1;
fclose($fichier);

#################################################


# :: LISTE INITIALE DES PARTICIPANTS  ::

#TEST SUR LES LIMITES DES EFFECTIFS FOURNIES
//Si le nombre des participants est en dehors de l'intervale [2;63]///////////////////////////////////////////
if($nblines<2 || $nblines>63){

		echo "<h3>Impossible d'utiliser l'outil premier tour ! VOIR ERROR TYPE </h3>";
		//$_error=trigger_error(" ERROR TYPE : Nombre de participants < 2 ou >63 </h2>", E_USER_ERROR);
		echo "<button  class=\"btn default \" id=\"error\" onclick=\"affich_error()\">ERROR TYPE</button>";
		echo "<div id=\"u_error\"> </div>";
require "FichiersJS/errMonitor.js";	
}


//Sinon si le nombre des participants est bien dans l'intervale [2;63]////////////////////////////////////////
 else {	
	$fichier=fopen("uploaded_files/".$file_name,"r");
     echo "<h1 class=\"hypertext1 text-center text-uppercase mb-5\">Liste des participants  </h1>";
         echo "<table class=\"table table-striped\" id=\"liste_ini\">";
     echo "<tbody >";

#AFFICHAGE ET ENREGISTREMENT DES PARTICIPANTS
$n=0; 
while($ligne=fgetcsv($fichier,1000,",")){
	if($n===0){	echo "<tr id=\"$n\"> <th  scope=\"row\">#</th>";
		echo "<th> $ligne[0]</th> <th>$ligne[1]</th> <th>$ligne[2]</th> <th>$ligne[3]</th>";
	echo "</tr>";}
	else{
	echo "<tr id=\"$n\"> <th  scope=\"row\">$n</th>";
		echo "<td> $ligne[0]</td> <td>$ligne[1]</td> <td>$ligne[2]</td> <td>$ligne[3]</td>";
	echo "</tr>";}
$n++;
if(($ligne[0]!=='NOM') && ($ligne[3]!=='CLUB')){
	$liste_joueurs[]=$ligne[0]." ".$ligne[1];
	$player=array($ligne[0],$ligne[1],$ligne[3]);#Utilisation de structure de liste
	$all_players[]=$player;
 }
}
		echo "<p id=\"impt\">$nblines. lignes importées</p>";
	echo "</tbody></table>";

 #:::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::
 
 
#CLICK BUTTONS
echo " <div  id=\"btn_div\" >";
echo "<a href=\"tirageAuSort.php#matchs\">
		<button  class=\"btn success\" id=\"button_tirage\" onclick=\"Func_affich()\">Lancer le tirage au sort</button></a>";
echo "</div><br><br>";



if(!puissance_de_2($nblines)){
	
    #########################################################################################################################################
	#::::::::::::::::::::::::::::           NOMBRE DE PARTICIPANTS N'EST PAS PUISSANCE DE 2          :::::::::::::::::::::::::::::::::::::::#    

	
	
	
	#FONCTION : RECHERCHE DE PUISSANCE DE 2 SUPÉRIEUR 	
	function puiss_de_2_superieur($n){
		for($i=1;$i<7;$i++){
			if($n>pow(2,$i)){$puiss_sup=pow(2,$i+1);}
		}
		return $puiss_sup;
	}

	#DONNÉES CALCULÉ SELON LE MODE ELIMINATOIRE DIRECTE ou {nombre des participants != puissance de 2 }
	$joueurs_qualifiés_2erTour=puiss_de_2_superieur($nblines)-$nblines;
	$joueurs_disponibles=$nblines-$joueurs_qualifiés_2erTour;
	$nbr_tour_prelim=1;
	while($joueurs_disponibles%2!=0){$nbr_tour_prelim++;}
		$joueurs_fictifs=$joueurs_qualifiés_2erTour*$nbr_tour_prelim;

	$nbr_participants_2emeTr=$joueurs_fictifs+($joueurs_disponibles/2);
	$joueurs_exempts=$joueurs_qualifiés_2erTour;
	$nbr_total_participants=$joueurs_disponibles+$joueurs_qualifiés_2erTour;
	
	



	#ENREGISTREMENT DES JOUEURS EN TOUR PRELIMINAIRE

	#ENREGISTREMENT PARTICIPANTS AU 1ER TOUR
	$matchs_dispo=$joueurs_disponibles/2;
	$jd=$joueurs_disponibles; 
	while($matchs_dispo>0){
		$_1erTr[]=$all_players[$jd-1];
		$_1erTr[]=$all_players[$jd-2];
			$jd-=2;
		$matchs_dispo--;
	}

	# ENREGISTREMENT DES RENCONTRES PRELIMINAIRES 
	$scores_1erTr=scores_joueurs($joueurs_disponibles);
	$Tour_prelim=trier_par_club($_1erTr);
	$disponibles=$joueurs_disponibles;
	while($disponibles>0){
		$_1erTr_avecScr[]=array($Tour_prelim[$disponibles-1],$scores_1erTr[$disponibles-1]);# affectation des scores
			$disponibles--;
	}




	#FONCTION: RETOURNE DISQUALIFIES DU TOUR PRELIMINAIRE PREND 2 PARAMETTRES (TABLEAUX DE LISTE A DEUX CHAMPS, .IDEN) 
	function les_disqualifies($tab_joueurs,$vainqueurs){
		$index=count($tab_joueurs)-1;
		$qlf=array_column($vainqueurs,0);
		while($index>=0){
			if(!in_array($tab_joueurs[$index][0],$qlf)){
				$disqualifies[]=$tab_joueurs[$index][0];
			}
			$index--;
		}
	return $disqualifies;
	}


	#FONCTION : AFFICHE LES RESULTATS DES RENCONTRES DU TOUR PRELIMINAIRE
	function affiche_Tour_prelimn($tab_joueurs){
		$index=count($tab_joueurs)-1;
		$infos_jouer=array_column($tab_joueurs,0);
		echo "<div id=\"R_prelim\"> Resultats des rencontres preliminaires<br>";
			while($index>=0){
				$j=$index-1;
				$value0=$infos_jouer[$index];
				$value1=$infos_jouer[$j];
				$score0=$tab_joueurs[$index];
				$score1=$tab_joueurs[$j];
					echo "<div class=\"btn-group mt-2 mb-4\" role=\"group\" > ";
						echo "<a  class=\"btn default\">$value0[0] $value0[1] <br> Score : $score0[1] </a>";
						echo "<a  class=\"btn default\">$value1[0] $value1[1] <br> Score : $score1[1] </a>";
					echo "</div><br>";
			$index-=2;
			}
		echo "</div>";
	}

	#FONCTION : AFFICHE LES DISQUALIFIÉS
	function affiche_elimines($tab_joueurs){
		$index=count($tab_joueurs)-1;
		echo "<div id=\"disqualfied\"> Les joueurs eliminés au tour preliminaire<br>";
		while($index>=0){
			$j=$index-1;
			$value0=$tab_joueurs[$index];
				echo "<div id =\"elimn\"class=\"btn-group mt-2 mb-4\" role=\"group\" aria-label=\"actionButtons\"> ";
					echo "<a  class=\"btn default\">$value0[0] $value0[1]  </a>";
				echo "</div><br>";
			$index--;
		}
	echo "</div>";
	}

	


	#ENREGISTREMENT DES JOUERS EXEMPTES 1EME TOUR
	$scores_exemptes=scores_joueurs($joueurs_exempts*2);
	$nbr_exempts=$joueurs_exempts;
	while($nbr_exempts>0){
	
		$les_exempts[]=$all_players[$nbr_total_participants-1];
			$nbr_exempts--;
			$nbr_total_participants--;
	}
	
	shuffle($les_exempts);
	$nombre_xmpt=$joueurs_exempts;
	while($nombre_xmpt>0){
		$les_exempts_avecScr[]=array($les_exempts[$nombre_xmpt-1],$scores_exemptes[$nombre_xmpt-1]);
			$nombre_xmpt--;
	}




	#ENREGISTREMENT DES JOUEURS 2EME TOUR AVEC LEUR SCORES
	$les_vainqueurs_1erTr=vainqueurs($_1erTr_avecScr);
	$joueurs_2eme_Tour=array_merge($les_vainqueurs_1erTr,$les_exempts_avecScr);

	#ENREGISTREMENT ET AFFICHAGE DES JOUEURS ELIMINÉES AU TOUR PRELIMINAIRE
	$les_disqualifies_TrPrelimn=les_disqualifies($_1erTr_avecScr,$les_vainqueurs_1erTr);
	


	#ENREGISTREMENT DES JOUEURS 2EME TOUR
	$rencontres_2emeTr=array_column($joueurs_2eme_Tour,0);
	afficher_rencontres($rencontres_2emeTr);
	
	
	echo "<div class=\" btn-group mt-2 mb-4 \" role=\"group\" >";	
	echo "<a href=\"tirageAuSort.php#disqualfied\">
			<button  class=\"btn default\"  onclick=\"Func_elimin()\">Les joueurs eliminées au tour preliminaire
			</button></a>";
	echo "<a href=\"tirageAuSort.php#R_prelim\">
			<button  class=\"btn default\"  onclick=\"result_TrPrelim()\">Afficher les Rencontres preliminaire
			</button></a>";
	echo "</div><br>";

	affiche_Tour_prelimn($_1erTr_avecScr);
	affiche_elimines($les_disqualifies_TrPrelimn);

	##################################################################################################
	#READY TO LOAD:IMPLEMENTATION DE LA LISTE DES RENCONTRES COMPLETE DU PREMIER TOUR EN ARBORÉSCENCE 
	
	
	#AFFECTATION DE SCORES ET ENREGISTREMENT DES VAINQUEURS
	$rencontres_2emeTr_avecSCR=affecter_scores($rencontres_2emeTr);#### LISTE DES RENCONTRES PREMIER TOUR *A réutiliser en arboréscence JS *
	$nombre_joueurs_ttl=count($rencontres_2emeTr_avecSCR);
	$nbr_tours=nombre_de_tours($nombre_joueurs_ttl);

	echo " <div class=\"alert alert-success \">";
		echo  "<strong>$nbr_tours Tours prévues !</strong>vous devez lancer le tirage au sort pour pouvoir commencer <br> 
				la visualisation de l'arbre des rencontres </div>";     
	echo "<br>";
	
echo " <div  id=\"btn_div\" >";
echo "<a href=\"tirageAuSort.php#OrganiseChart6\">
		<button  class=\"btn success\" id=\"button_arbre\" onclick=\"Func_affich_arbre()\">Afficher l'arbre des rencontres </button></a>";
echo "</div><br><br>";

			
	##################################################  CHRONOLOGIE DE LA COMPETITION 	#########################################
	
	for($i=0;$i<=$nbr_tours;$i++){
		#CASE $i :
		//$i == 0 <=> PREMIER TOUR   
		//$i == 1 <=> 2eme TOUR 
		//$i == 2 <=> 3EME TOUR 
		//$i == 3 <=> 4EME TOUR 
		//$i == 4 <=> 5EME TOUR 
		##########################
		  
		if($i==0){
			${"les_vainqueurs".$i}=($rencontres_2emeTr_avecSCR);
			$y=0;
			foreach(${"les_vainqueurs".$i} as $key => $value){
				${"scores".$i}[]=$value[1];							 // @SCORES 		  1
				${"nom_prenom".$i}[]=array($value[0][0],$value[0][1]); // @0NOMS @1PRENOMS  1
			${"scr".$i}=${"scores".$i}[$y];
				${"nom".$i}=${"nom_prenom".$i}[$y][0] ;
				${"prenom".$i}=${"nom_prenom".$i}[$y][1];	
				${"nom_et_prenom".$i}[]=${"nom".$i}." ".${"prenom".$i}."   [".${"scr".$i}."]";
				$y++;
			}	


		}
		if($i!=0){
			 $j=$i-1;
				${"les_vainqueurs".$i}=vainqueurs(${"les_vainqueurs".$j});
				$x=0;
				foreach(${"les_vainqueurs".$i} as $key => $value){
					
					${"scores".$i}[]=$value[1];							 // @SCORES 		  1
					${"nom_prenom".$i}[]=array($value[0][0],$value[0][1]); // @0NOMS @1PRENOMS  1
				${"scr".$i}=${"scores".$i}[$x];
					${"nom".$i}=${"nom_prenom".$i}[$x][0] ;
					${"prenom".$i}=${"nom_prenom".$i}[$x][1];	
					${"nom_et_prenom".$i}[]=${"nom".$i}." ".${"prenom".$i}."  [".${"scr".$i}."]";
					$x++;
				}	
		}		
			
	}

# SYNCHRONYSATION DES DONNÉES DANS LA STRUTURE ARBRE DÉFINIE SUR JS	
if($nbr_tours==5){
echo "<div class=\"chart\" id=\"OrganiseChart6\"> </div> ";
	echo  "<script> new Treant(creatTree32('$nom_et_prenom0[0]','$nom_et_prenom0[1]','$nom_et_prenom0[2]','$nom_et_prenom0[3]','$nom_et_prenom0[4]',
	'$nom_et_prenom0[5]','$nom_et_prenom0[6]','$nom_et_prenom0[7]','$nom_et_prenom0[8]','$nom_et_prenom0[9]','$nom_et_prenom0[10]','$nom_et_prenom0[11]',
	'$nom_et_prenom0[12]','$nom_et_prenom0[13]','$nom_et_prenom0[14]','$nom_et_prenom0[15]',      
	'$nom_et_prenom0[16]','$nom_et_prenom0[17]','$nom_et_prenom0[18]','$nom_et_prenom0[19]','$nom_et_prenom0[20]','$nom_et_prenom0[21]','$nom_et_prenom0[22]',
	'$nom_et_prenom0[23]','$nom_et_prenom0[24]','$nom_et_prenom0[25]','$nom_et_prenom0[26]','$nom_et_prenom0[27]','$nom_et_prenom0[28]','$nom_et_prenom0[29]',
	'$nom_et_prenom0[30]','$nom_et_prenom0[31]',     '$nom_et_prenom1[15]','$nom_et_prenom1[14]',
	'$nom_et_prenom1[13]','$nom_et_prenom1[12]','$nom_et_prenom1[11]','$nom_et_prenom1[10]','$nom_et_prenom1[9]','$nom_et_prenom1[8]','$nom_et_prenom1[7]','$nom_et_prenom1[6]',
	'$nom_et_prenom1[5]','$nom_et_prenom1[4]','$nom_et_prenom1[3]','$nom_et_prenom1[2]','$nom_et_prenom1[1]','$nom_et_prenom1[0]','$nom_et_prenom2[0]',
	'$nom_et_prenom2[1]', '$nom_et_prenom2[2]', '$nom_et_prenom2[3]',
	'$nom_et_prenom2[4]', '$nom_et_prenom2[5]', '$nom_et_prenom2[6]', '$nom_et_prenom2[7]' ,      '$nom_et_prenom3[3]', '$nom_et_prenom3[2]', '$nom_et_prenom3[1]', 
	'$nom_et_prenom3[0]',   '$nom_et_prenom4[0]', '$nom_et_prenom4[1]',  '$nom_et_prenom4[0]'   ));</script>";


}elseif($nbr_tours==4){

	echo "<div class=\"chart\" id=\"OrganiseChart6\"> </div> ";
	echo  "<script> new Treant(creatHF2('$nom_et_prenom0[0]','$nom_et_prenom0[1]','$nom_et_prenom0[2]','$nom_et_prenom0[3]','$nom_et_prenom0[4]',
	'$nom_et_prenom0[5]','$nom_et_prenom0[6]','$nom_et_prenom0[7]','$nom_et_prenom0[8]','$nom_et_prenom0[9]','$nom_et_prenom0[10]',
	'$nom_et_prenom0[11]','$nom_et_prenom0[12]','$nom_et_prenom0[13]','$nom_et_prenom0[14]','$nom_et_prenom0[15]',  '$nom_et_prenom1[7]', 
	'$nom_et_prenom1[6]', '$nom_et_prenom1[5]', '$nom_et_prenom1[4]', '$nom_et_prenom1[3]', '$nom_et_prenom1[2]',   '$nom_et_prenom1[1]', 
	'$nom_et_prenom1[0]', '$nom_et_prenom2[0]', '$nom_et_prenom2[1]', '$nom_et_prenom2[2]', '$nom_et_prenom2[3]' ,     '$nom_et_prenom3[1]', 
	'$nom_et_prenom3[0]',    '$nom_et_prenom4[0]'  ));</script>";

	

}elseif($nbr_tours==3){

	echo "<div class=\"chart\" id=\"OrganiseChart6\"> </div> ";
	echo  "<script> new Treant( creatQF2('$nom_et_prenom0[0]', '$nom_et_prenom0[1]', '$nom_et_prenom0[2]', '$nom_et_prenom0[3]', '$nom_et_prenom0[4]', 
	'$nom_et_prenom0[5]', '$nom_et_prenom0[6]', '$nom_et_prenom0[7]',   '$nom_et_prenom1[3]',   '$nom_et_prenom1[2]',  '$nom_et_prenom1[1]', 
	'$nom_et_prenom1[0]', '$nom_et_prenom2[0]', '$nom_et_prenom2[1]', '$nom_et_prenom3[0]'));</script>";

}elseif($nbr_tours==2){
	 
echo "<div class=\"chart\" id=\"OrganiseChart6\"> </div> ";
	echo  "<script> new Treant( creatDF2('$nom_et_prenom0[0]', '$nom_et_prenom0[1]', '$nom_et_prenom0[2]', '$nom_et_prenom0[3]', '$nom_et_prenom1[1]', 
	'$nom_et_prenom1[0]', '$nom_et_prenom2[0]'));</script>";

}else{
	echo "<div class=\"chart\" id=\"OrganiseChart6\"> </div> ";
	echo  "<script> new Treant( creatFinale('$nom_et_prenom0[0]', '$nom_et_prenom0[1]', '$nom_et_prenom1[0]',));</script>";
}
	
	

	echo "<h3> Informations utiles </h3>";
	echo "<footer class=\"footer\">
				Nombre de tours préliminaires = $nbr_tour_prelim <br>
				Nombre des joueurs participants au tour prelimnaire= $joueurs_disponibles <br>
				Nombre joueurs exmempts du premier tour = $joueurs_exempts<br>
				Nombre de participants au deuxième tour = .$nbr_participants_2emeTr  </footer><br>";

}        #########################################################################################################################



else{ #########################################################################################################################################
	  #::::::::::::::::::::::::::::           NOMBRE DE PARTICIPANTS EST PUISSANCE DE 2          :::::::::::::::::::::::::::::::::::::::::::::#    
	





//Enregistrement et Affichage des rencontres 1er Tour 		 
$_rencontres=trier_par_club($all_players);
afficher_rencontres($_rencontres);
//Enregistrement vainqueurs 1er Tour
$rencontres_2emeTr_avecSCR_P2=affecter_scores($_rencontres);

	$nbr_tours=nombre_de_tours($nblines);
	echo " <div class=\"alert alert-success \">";
	echo  "<strong>$nbr_tours Tours prévues !</strong>vous devez lancer le tirage au sort pour pouvoir commencer <br> 
				la visualisation de l'arbre des rencontres </div>";     
	echo "<br>";

	echo " <div  id=\"btn_div\" >";
	echo "<a href=\"tirageAuSort.php#OrganiseChart6\">
		<button  class=\"btn success\" id=\"button_arbre\" onclick=\"Func_affich_arbre()\">Afficher l'arbre des rencontres </button></a>";
	echo "</div><br><br>";

			

			
	############################################   CHRONOLOGIE DE LA COMPETITION   ###########################################################	
	
for($i=0;$i<=$nbr_tours;$i++){
		#CASE $i :
		//$i == 0 <=> PREMIER TOUR   
		//$i == 1 <=> 2eme TOUR 
		//$i == 2 <=> 3EME TOUR 
		//$i == 3 <=> 4EME TOUR 
		//$i == 4 <=> 5EME TOUR 
		##########################
		  
		if($i==0){
			${"les_vainqueurs".$i}=($rencontres_2emeTr_avecSCR_P2);
			$y=0;
			foreach(${"les_vainqueurs".$i} as $key => $value){
				${"scores".$i}[]=$value[1];							 // @SCORES 		  1
				${"nom_prenom".$i}[]=array($value[0][0],$value[0][1]); // @0NOMS @1PRENOMS  1
					${"scr".$i}=${"scores".$i}[$y];
					${"nom".$i}=${"nom_prenom".$i}[$y][0] ;
					${"prenom".$i}=${"nom_prenom".$i}[$y][1];	
					${"nom_et_prenom".$i}[]=${"nom".$i}." ".${"prenom".$i}." [".${"scr".$i}."]";
				$y++;
			}	


		}
		if($i!=0){
			 $j=$i-1;
				${"les_vainqueurs".$i}=vainqueurs(${"les_vainqueurs".$j});
				$x=0;
				foreach(${"les_vainqueurs".$i} as $key => $value){
					
					${"scores".$i}[]=$value[1];							 // @SCORES 		  1
					${"nom_prenom".$i}[]=array($value[0][0],$value[0][1]); // @0NOMS @1PRENOMS  1
						${"scr".$i}=${"scores".$i}[$x];
						${"nom".$i}=${"nom_prenom".$i}[$x][0] ;
						${"prenom".$i}=${"nom_prenom".$i}[$x][1];	
						${"nom_et_prenom".$i}[]=${"nom".$i}." ".${"prenom".$i}." [".${"scr".$i}."]";
					$x++;
				}	
		}		
			
}



# SYNCHRONYSATION DES DONNÉES DANS LA STRUTURE ARBRE DÉFINIE SUR JS
if($nbr_tours==5){
echo "<div class=\"chart\" id=\"OrganiseChart6\"> </div> ";
	echo  "<script> new Treant(creatTree32('$nom_et_prenom0[0]','$nom_et_prenom0[1]','$nom_et_prenom0[2]','$nom_et_prenom0[3]','$nom_et_prenom0[4]',
	'$nom_et_prenom0[5]','$nom_et_prenom0[6]','$nom_et_prenom0[7]','$nom_et_prenom0[8]','$nom_et_prenom0[9]','$nom_et_prenom0[10]','$nom_et_prenom0[11]',
	'$nom_et_prenom0[12]','$nom_et_prenom0[13]','$nom_et_prenom0[14]','$nom_et_prenom0[15]',      
	'$nom_et_prenom0[16]','$nom_et_prenom0[17]','$nom_et_prenom0[18]','$nom_et_prenom0[19]','$nom_et_prenom0[20]','$nom_et_prenom0[21]','$nom_et_prenom0[22]',
	'$nom_et_prenom0[23]','$nom_et_prenom0[24]','$nom_et_prenom0[25]','$nom_et_prenom0[26]','$nom_et_prenom0[27]','$nom_et_prenom0[28]','$nom_et_prenom0[29]',
	'$nom_et_prenom0[30]','$nom_et_prenom0[31]',     '$nom_et_prenom1[15]','$nom_et_prenom1[14]',
	'$nom_et_prenom1[13]','$nom_et_prenom1[12]','$nom_et_prenom1[11]','$nom_et_prenom1[10]','$nom_et_prenom1[9]','$nom_et_prenom1[8]','$nom_et_prenom1[7]','$nom_et_prenom1[6]',
	'$nom_et_prenom1[5]','$nom_et_prenom1[4]','$nom_et_prenom1[3]','$nom_et_prenom1[2]','$nom_et_prenom1[1]','$nom_et_prenom1[0]','$nom_et_prenom2[0]',
	'$nom_et_prenom2[1]', '$nom_et_prenom2[2]', '$nom_et_prenom2[3]',
	'$nom_et_prenom2[4]', '$nom_et_prenom2[5]', '$nom_et_prenom2[6]', '$nom_et_prenom2[7]' ,      '$nom_et_prenom3[3]', '$nom_et_prenom3[2]', '$nom_et_prenom3[1]', 
	'$nom_et_prenom3[0]',   '$nom_et_prenom4[0]', '$nom_et_prenom4[1]',  '$nom_et_prenom4[0]'   ));</script>";


}elseif($nbr_tours==4){

	 echo "<div class=\"chart\" id=\"OrganiseChart6\"> </div> ";
	echo  "<script> new Treant(creatHF2('$nom_et_prenom0[0]','$nom_et_prenom0[1]','$nom_et_prenom0[2]','$nom_et_prenom0[3]','$nom_et_prenom0[4]',
	'$nom_et_prenom0[5]','$nom_et_prenom0[6]','$nom_et_prenom0[7]','$nom_et_prenom0[8]','$nom_et_prenom0[9]','$nom_et_prenom0[10]',
	'$nom_et_prenom0[11]','$nom_et_prenom0[12]','$nom_et_prenom0[13]','$nom_et_prenom0[14]','$nom_et_prenom0[15]',  '$nom_et_prenom1[7]', 
	'$nom_et_prenom1[6]', '$nom_et_prenom1[5]', '$nom_et_prenom1[4]', '$nom_et_prenom1[3]', '$nom_et_prenom1[2]',   '$nom_et_prenom1[1]', 
	'$nom_et_prenom1[0]', '$nom_et_prenom2[0]', '$nom_et_prenom2[1]', '$nom_et_prenom2[2]', '$nom_et_prenom2[3]' ,     '$nom_et_prenom3[1]', 
	'$nom_et_prenom3[0]',    '$nom_et_prenom4[0]'  ));</script>";

	

}elseif($nbr_tours==3){

	echo "<div class=\"chart\" id=\"OrganiseChart6\"> </div> ";
	echo  "<script> new Treant( creatQF2('$nom_et_prenom0[0]', '$nom_et_prenom0[1]', '$nom_et_prenom0[2]', '$nom_et_prenom0[3]', '$nom_et_prenom0[4]', 
	'$nom_et_prenom0[5]', '$nom_et_prenom0[6]', '$nom_et_prenom0[7]',   '$nom_et_prenom1[3]',   '$nom_et_prenom1[2]',  '$nom_et_prenom1[1]', 
	'$nom_et_prenom1[0]', '$nom_et_prenom2[0]', '$nom_et_prenom2[1]', '$nom_et_prenom3[0]'));</script>";

}elseif($nbr_tours==2){
	 
	echo "<div class=\"chart\" id=\"OrganiseChart6\"> </div> ";

	echo  "<script> new Treant( creatDF2('$nom_et_prenom0[0]', '$nom_et_prenom0[1]', '$nom_et_prenom0[2]', '$nom_et_prenom0[3]', '$nom_et_prenom1[1]', 
	'$nom_et_prenom1[0]', '$nom_et_prenom2[0]'));</script>";
	
}else{
	echo "<div class=\"chart\" id=\"OrganiseChart6\"> </div> ";
	echo  "<script> new Treant( creatFinale('$nom_et_prenom0[0]', '$nom_et_prenom0[1]', '$nom_et_prenom1[0]',));</script>";
}
	
}   ########################################################################################################################################




##################################################### GENERATEUR DE DOC PDF #####################################################################


# CREATION DE LA CLASS FILLE PDF QUI HERITE DE FPDF
# MODEL OBJET
class PDF extends FPDF { 
  // Header 
  function Header() { 
    # Logo : 8 >position à gauche du document (en mm), 2 >position en haut du document, 80 >largeur de l'image en mm). 
	# La hauteur est calculée automatiquement. 
   	$this->Image('fpdf181/logoPT.png',90,2,20);
    # Saut de ligne 20 mm 
    $this->Ln(20); 
 
    # Titre gras (B) police Helbetica de 11 
    $this->SetFont('Helvetica','B',11); 
    # fond de couleur gris (valeurs en RGB) 
    $this->setFillColor(230,230,230); 
    # position du coin supérieur gauche par rapport à la marge gauche (mm) 
    $this->SetX(70); 
    # Texte : 60 >largeur ligne, 8 >hauteur ligne. Premier 0 >pas de bordure, 1 >retour à la ligneensuite, C >centrer texte, 1> couleur de fond ok   
    $this->Cell(60,8,'DOCUMENT RECAPULATIF',0,1,'C',1); 
    # Saut de ligne 10 mm 
    $this->Ln(10);     
  } 
  
  # SECTION FOOTER : PIED DE PAGE 
  function Footer() { 
    $this->SetY(-15); # Positionnement à 1,5 cm du bas 
    $this->SetFont('Helvetica','I',9); # Police Arial italique 8 
	$this->setX(70);
	$this->Cell(0,10,'Document generé le '.date('d-m-Y'),0,0,'C');
    # Numéro de page, centré (C) 
    $this->Cell(0,10,'Page '.$this->PageNo().'/{nb}',0,0,'C');
  } 
}

#INSTANCIATION D'UN NOUVEAU DOC PDF 
$pdf = new PDF('P','mm','A4'); 
# Nouvelle page A4 (incluant ici logo, titre et pied de page) 
$pdf->AddPage(); 
# Polices par défaut : Helvetica taille 9 
$pdf->SetFont('Helvetica','',9); 
# Couleur par défaut : noir 
$pdf->SetTextColor(0); 
# Compteur de pages {nb} 
$pdf->AliasNbPages();
$pdf->setX(70);
$pdf->Cell(55,8,'Liste des rencontres du premier tour ',1);


# FONCTION : ERITURE  EN TÊTE DES TABLEAUX EN 4 COLONNES DE LARGEURS VARIABLES 
function ecrire_tableau_entete($position_entete,$tab_joueurs) { 
$nbr_participants=count($tab_joueurs);
$tous_les_participants=$tab_joueurs;

  global $pdf; 
  $pdf->SetDrawColor(183); // Couleur du fond RVB 
  $pdf->SetFillColor(221); // Couleur des filets RVB 
  $pdf->SetTextColor(0); // Couleur du texte noir 
  $pdf->SetY($position_entete); 
  // position de colonne 1 (10mm à gauche)   
  $pdf->SetX(25); 
  $pdf->Cell(50,8,'NOM',1,0,'C',1);  // 60 >largeur colonne, 8 >hauteur colonne 
  // position de la colonne 2 (70 = 10+60) 
  $pdf->SetX(75);  
  $pdf->Cell(50,8,'PRENOM',1,0,'C',1); 
  // position de la colonne 3 (130 = 70+60) 
  $pdf->SetX(125);  
  $pdf->Cell(50,8,'CLUB',1,0,'C',1); 
  $pdf->Ln(); // Retour à la ligne 
  
  # INSERER TANT QUE LA LISTE DES RENCONTRES N'EST PAS VIDE
  while($nbr_participants>0){
	  $value0=$tous_les_participants[$nbr_participants-1];
	  $value1=$tous_les_participants[$nbr_participants-2];
		#1er Joueur
				$pdf->SetX(25);  
				$pdf->Cell(50,8,"$value0[0]",1,0,'C',1); 
					$pdf->SetX(75);  
					$pdf->Cell(50,8,"$value0[1]",1,0,'C',1);
						$pdf->SetX(125);  
						$pdf->Cell(50,8,"$value0[2]",1,0,'C',1);
		$pdf->ln();
		#2eme Joueur
				$pdf->SetX(25);  
				$pdf->Cell(50,8,"$value1[0]",1,0,'C',1); 
					$pdf->SetX(75);  
					$pdf->Cell(50,8,"$value1[1]",1,0,'C',1);
						$pdf->SetX(125);  
						$pdf->Cell(50,8,"$value1[2]",1,0,'C',1);
		$pdf->ln();
		$pdf->ln();	
	$nbr_participants-=2;
  }
	#INFORMATION SUPPLÉMENTAIRES : EN BAS DE LA LISTE DES RENCONTRES
	$n=count($tab_joueurs);
	$r=$n/2;
	$pdf->setX(25);
	$pdf->Cell(0,10,"Nombre de participants au premier tour : $n",0,0);
	$pdf->ln();
	$pdf->setX(25);
	$pdf->Cell(0,10,"Nombre de rencontres a jouer: $r",0,0);
  
} 



# ECRITURE DE LA LISTE DES RENCONTRES PREMIER TOUR 
  
// POLICE DES CARACTÈRES 
$pdf->SetFont('Helvetica','',9); 
$pdf->SetTextColor(0); 

# IMPLEMENTATION DE LA FONCTION ECRIRE_TABLEAU_ENTETE SELON NOMBRE DE PARTICIPANTS
if(!puissance_de_2($nblines)){
	ecrire_tableau_entete(70,$rencontres_2emeTr);
 }
 else{
	 ecrire_tableau_entete(70,$_rencontres);
 }

#TELECHARGEMENT DU DOCUMENT  
$download=$pdf->Output('Telechargements/recapulatif.pdf','F');

echo "<div  class=\"btn-group mt-2 mb-4\" id=\"telechargement\">";
echo "<a href=\"Telechargements/recapulatif.pdf\" class=\"btn success\" > Télécharger un recapulatif</a> </div>";

##################################################### GENERATOR de DOC #####################################################################
#####################################################                  #####################################################################

#FERMÉTURE DE NOTRE BASE DE DONNÉES  CSV / XLSX
fclose($fichier);	
}#fin sinon::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::  




}
require "FichiersJS/monitor.js";
?>

