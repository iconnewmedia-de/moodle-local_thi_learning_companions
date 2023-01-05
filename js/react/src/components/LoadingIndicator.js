export default function LoadingIndicator({loading}) {
    if (!loading) {
        return null;
    } else {
        return (
            <div className='learningcompanions_loading'></div>
        );
    }
}
