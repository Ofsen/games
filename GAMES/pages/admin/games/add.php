<?php

$app = App::getInstance();

$gameTable = $app->getTable('Game');
$gpTable = $app->getTable('Game_plat');

date_default_timezone_set("Europe/Amsterdam");
$now = new DateTime();
$dat = $now->format('Y-m-d H:i:s'); 

if (isset($_POST['action'])) {
	if(!empty($_POST['titre']) && !empty($_POST['descr']) && !empty($_POST['dev']) && !empty($_POST['plats']) && !empty($_FILES['img']['name']) && !empty($_FILES['img']['tmp_name']) && !empty($_POST['price'])) {

		$name = str_replace(" ", "-", $_FILES['img']['name']);
		$tmpName = $_FILES['img']['tmp_name'];
		$ext = strrchr($name, ".");
		$errors = $_FILES['img']['error'];
		$dest = './img/game/' . $name;
		$goodExt = array('.jpg', '.png');
		
		if(is_int($_POST['price'])) {
			if(in_array($ext, $goodExt)) {
				if ($errors == 0) {
					if (move_uploaded_file($tmpName, $dest)) {
						$result = $gameTable->create([
							'titre' => htmlspecialchars($_POST['titre']),
							'img' => htmlspecialchars($dest),
							'descr' => htmlentities($_POST['descr'], ENT_QUOTES | ENT_XML1, 'UTF-8'),
							'dev' => htmlspecialchars($_POST['dev']),
							'dat' => $dat,
							'price' => htmlspecialchars($_POST['price'])						
							]);
						if($result) {
							$lastInsertId = App::getInstance()->getDb()->lastInsertId();
							foreach($_POST['plats'] as $value) {
								$res = $gpTable->create([
									'game_id' => $lastInsertId,
									'plat_id' => htmlspecialchars($value)
								]);
							}
							if($res) {
								header('Location: admin.php?p=games.edit&id=' . $lastInsertId);
							} else {
								?>
								<div class="danger">
									Erreur : l'artice n'a pas été ajoutée. Note: Problème lors de l'insertion des plateformes.
								</div>
								<?php
							}
						} else {
							?>
							<div class="danger">
								Erreur : l'artice n'a pas été ajoutée. Note: Problème lors de l'insertion du jeu.
							</div>
							<?php
						}
					} else {
						?>
						<div class="danger">
							Erreur : non uploader.
						</div>
						<?php
					}
				} else {
					?>
					<div class="danger">
						Erreur : veuillez reuploader l'image.
					</div>
					<?php
				}
			} else {
			?>
			<div class="danger">
				Erreur : format d'image incorrect.
			</div>
			<?php
			}
		} else {
			?>
			<div class="danger">
				Erreur : Le prix doit être un chiffre.
			</div>
			<?php
		}
	} else {
		?>
		<div class="danger">
			Erreur : Tout les champs sont obligatoires.
		</div>
		<?php
	}
}

$plats = $app->getTable('Platform')->extract('id', 'nom');
$form = new \App\HTML\GamesForm($_POST);

?>

<h4>Nouveau jeu</h4>
<hr>

<form method="POST" class="add" enctype="multipart/form-data">
		<?= $form->input('titre', 'Titre'); ?>
		<div class="addPlats">
			<label>Plateformes</label>
		<?php
		foreach ($app->getTable('Platform')->all() as $plat) {
			echo $form->input($plat->nom, $plat->nom, ['type' => 'checkbox', 'value' => $plat->id, 'name' => 'plats[]']); 
		}?>
		</div>
		<?= $form->input('price', 'Prix'); ?>
		<?= $form->input('img', 'Image', ['type' => 'file']); ?>
		<?= $form->input('descr', 'Déscription', ['type' => 'textarea']); ?>
		<?= $form->input('dev', 'Développeur'); ?>
		<button type="submit" name="action">Sauvegarder</button>
</form>