<?php

class EditcountHTML extends Editcount {
	/** @var int[] */
	private $nscount;

	/** @var int */
	private $total;

	/**
	 * Output the HTML form on Special:Editcount
	 *
	 * @param string $username
	 * @param int $uid
	 * @param int[] $nscount
	 * @param int|null $total
	 */
	public function outputHTML( $username, $uid, array $nscount, $total = null ) {
		$this->nscount = $nscount;
		$this->total = $total ?: array_sum( $nscount );

		$this->setHeaders();

		$action = htmlspecialchars( $this->getPageTitle()->getLocalURL() );
		$user = $this->msg( 'editcount_username' )->escaped();
		$out = "
		<form id='editcount' method='post' action=\"$action\">
			<table>
				<tr>
					<td>$user</td>
					<td>" . new OOUI\TextInputWidget( [
						'name' => 'username',
						'value' => $username,
						'autofocus' => true,
					] ) . "</td>
					<td>" . new OOUI\ButtonInputWidget( [
						'label' => $this->msg( 'editcount_submit' )->text(),
						'flags' => [ 'primary', 'progressive' ],
						'type' => 'submit',
					] ) . " </td>
				</tr>";
		if ( $username != null && $uid != 0 ) {
			$editcounttable = $this->makeTable();
			$out .= "
				<tr>
					<td>&#160;</td>
					<td>$editcounttable</td>
					<td>&#160;</td>
				</tr>";
		}
		$out .= '
			</table>
		</form>';
		// @phan-suppress-next-line SecurityCheck-XSS
		$this->getOutput()->addHTML( $out );
	}

	/**
	 * Make the editcount-by-namespaces HTML table
	 *
	 * @return string
	 */
	private function makeTable() {
		$lang = $this->getLanguage();

		$total = $this->msg( 'editcount_total' )->escaped();
		$ftotal = $lang->formatNum( $this->total );
		$percent = wfPercent( $this->total ? 100 : 0 );
		// @fixme don't use inline styles
		$ret = "<table border='1' style='background-color: #fff; border: 1px #aaa solid; border-collapse: collapse;'>
				<tr>
					<th>$total</th>
					<th>$ftotal</th>
					<th>$percent</th>
				</tr>
		";

		foreach ( $this->nscount as $ns => $edits ) {
			$fedits = $lang->formatNum( $edits );
			$fns = ( $ns == NS_MAIN ) ? $this->msg( 'blanknamespace' ) : $lang->getFormattedNsText( $ns );
			$percent = wfPercent( $edits / $this->total * 100 );
			$fpercent = $lang->formatNum( $percent );
			$ret .= "
				<tr>
					<td>$fns</td>
					<td>$fedits</td>
					<td>$fpercent</td>
				</tr>
			";
		}
		$ret .= '</table>
		';

		return $ret;
	}
}
