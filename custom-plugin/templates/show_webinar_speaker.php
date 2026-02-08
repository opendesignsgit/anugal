<?php 
$image = get_field('keynote_speaker_image', get_the_ID());
$keynote_speaker_name = get_field( "keynote_speaker_name", get_the_ID() );
$keynote_speaker_experience = get_field( "keynote_speaker_experience", get_the_ID() );
$image2 = get_field('keynote_speaker_image_2', get_the_ID());
$keynote_speaker_name_2 = get_field( "keynote_speaker_name_2", get_the_ID() );
$keynote_speaker_experience_2 = get_field( "keynote_speaker_experience_2", get_the_ID() );
$image3 = get_field('keynote_speaker_image_3', get_the_ID());
$keynote_speaker_name_3 = get_field( "keynote_speaker_name_3", get_the_ID() );
$keynote_speaker_experience_3 = get_field( "keynote_speaker_experience_3", get_the_ID() );
?>
<div class="keynote_speaker_outer">
	<div class="keynote_speaker">
		<?php if( !empty( $image ) ): ?>
			<div class="keynote_speaker-img">
				<img src="<?php echo esc_url($image['sizes']['thumbnail']); ?>" alt="<?php echo esc_attr($image['alt']); ?>" />
			</div>
		<?php endif; ?>
		<?php if( !empty( $keynote_speaker_name ) ): ?>
			<span>KEYNOTE SPEAKER</span>
			<div class="keynote_speaker-name">
				<?php echo $keynote_speaker_name;?>
			</div>
		<?php endif; ?>
		<?php if( !empty( $keynote_speaker_experience ) ): ?>
			<div class="keynote_speaker-experience">
				<?php echo $keynote_speaker_experience;?>
			</div>
		<?php endif; ?>
	</div>
	<?php if( !empty( $image2 ) && !empty( $keynote_speaker_name_2 ) ): ?>
		<div class="keynote_speaker-2">
			<?php if( !empty( $image2 ) ): ?>
				<div class="keynote_speaker-img-2">
					<img src="<?php echo esc_url($image2['sizes']['thumbnail']); ?>" alt="<?php echo esc_attr($image2['alt']); ?>" />
				</div>
			<?php endif; ?>
			<?php if( !empty( $keynote_speaker_name_2 ) ): ?>
				<span>KEYNOTE SPEAKER</span>
				<div class="keynote_speaker-name-2">
					<?php echo $keynote_speaker_name_2;?>
				</div>
			<?php endif; ?>
			<?php if( !empty( $keynote_speaker_experience_2 ) ): ?>
				<div class="keynote_speaker-experience-2">
					<?php echo $keynote_speaker_experience_2;?>
				</div>
			<?php endif; ?>
		</div>
	<?php endif; ?>
	<?php if( !empty( $image3 ) && !empty( $keynote_speaker_name_3 ) ): ?>
		<div class="keynote_speaker-3">
			<?php if( !empty( $image3 ) ): ?>
				<div class="keynote_speaker-img-3">
					<img src="<?php echo esc_url($image3['sizes']['thumbnail']); ?>" alt="<?php echo esc_attr($image3['alt']); ?>" />
				</div>
			<?php endif; ?>
			<?php if( !empty( $keynote_speaker_name_3 ) ): ?>
				<span>KEYNOTE SPEAKER</span>
				<div class="keynote_speaker-name-3">
					<?php echo $keynote_speaker_name_3;?>
				</div>
			<?php endif; ?>
			<?php if( !empty( $keynote_speaker_experience_3 ) ): ?>
				<div class="keynote_speaker-experience-3">
					<?php echo $keynote_speaker_experience_3;?>
				</div>
			<?php endif; ?>
		</div>
	<?php endif; ?>
</div>